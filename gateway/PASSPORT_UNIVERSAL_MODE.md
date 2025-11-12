# Laravel Passport Universal Mode Setup

This application uses **Passport Universal Mode** for multi-tenant authentication, as recommended by [stancl/tenancy](https://tenancyforlaravel.com/docs/v3/integrations/passport).

## What is Universal Mode?

**Universal Mode** means that all Passport-related data (OAuth clients, tokens, etc.) is stored in the **central database**, NOT in individual tenant databases. Tokens are scoped to tenants using a `tenant_id` column.

### Benefits:

✅ **Single OAuth client** for all tenants (simpler management)
✅ **No need to create OAuth clients** for each tenant database
✅ **Centralized token management** - easier to revoke/monitor
✅ **Faster tenant provisioning** - no Passport setup needed
✅ **Reduced complexity** - one source of truth for auth

---

## Architecture

### Database Structure

```
Central Database (cec):
├── oauth_clients           // Shared by all tenants
├── oauth_access_tokens     // Contains tenant_id column
├── oauth_auth_codes        // Contains tenant_id column
├── oauth_refresh_tokens
├── oauth_personal_access_clients
└── users                   // Central/manager users

Tenant Database (tenant_{id}):
├── users                   // Tenant-specific users
├── orders
├── products
└── ... (tenant data only, NO oauth tables)
```

### Authentication Flow

#### Central Authentication (Manager)
```
1. POST manager.prindustry.test/api/v2/in/mgr/auth/login
2. User validated against central DB (users table)
3. Token created in oauth_access_tokens (tenant_id = NULL)
4. Token returned to client
```

#### Tenant Authentication
```
1. POST {subdomain}.prindustry.test/api/v1/mgr/login
2. Tenant identified by subdomain (InitializeTenancyByDomain middleware)
3. User validated against tenant DB (users table)
4. Token created in central oauth_access_tokens (tenant_id = tenant UUID)
5. Token returned to client
```

### Token Verification

When a tenant user makes an authenticated request:

```
1. Request → {subdomain}.prindustry.test/api/v1/mgr/...
2. Tenant identified (middleware)
3. Token loaded from central DB oauth_access_tokens
4. VerifyTenantToken middleware checks: token.tenant_id === current_tenant.id
5. If match → allow request
6. If mismatch → 403 Forbidden
```

---

## Setup Instructions

### 1. Run Migrations

The central database needs a `tenant_id` column in Passport tables:

```bash
php artisan migrate
```

This runs: `2024_11_12_000001_add_tenant_id_to_passport_tables.php`

### 2. Install Passport (Central DB Only)

```bash
php artisan passport:install
```

Save the output to your `.env`:

```env
PASSWORD_CLIENT_ID=your-client-id-here
PASSWORD_CLIENT_SECRET=your-client-secret-here
```

### 3. That's It!

No need to run Passport setup for each tenant. They all use the same OAuth clients from the central database.

---

## Configuration Files

### AuthServiceProvider

```php
// app/Providers/AuthServiceProvider.php

use App\Models\Passport\Token;
use App\Models\Passport\AuthCode;

public function boot()
{
    // Universal Mode: Use custom models that automatically add tenant_id
    Passport::useTokenModel(Token::class);
    Passport::useAuthCodeModel(AuthCode::class);

    // Token lifetimes
    Passport::tokensExpireIn(now()->addDays(5));
    Passport::refreshTokensExpireIn(now()->addDays(10));
    Passport::personalAccessTokensExpireIn(now()->addMonths(6));
}
```

### Passport Token Model

```php
// app/Models/Passport/Token.php

protected static function booted(): void
{
    static::creating(function ($token) {
        // Automatically add tenant_id when creating tokens
        if ($tenantId = tenant()?->id) {
            $token->tenant_id = $tenantId;
        }
    });
}
```

### Middleware

```php
// app/Http/Kernel.php

protected $routeMiddleware = [
    // ...
    'verify.tenant.token' => \App\Http\Middleware\VerifyTenantToken::class,
];
```

Apply to tenant routes that require authentication:

```php
Route::group([
    'middleware' => ['auth:tenant', 'verify.tenant.token']
], function () {
    // Tenant routes here
});
```

---

## Guards Configuration

```php
// config/auth.php

'guards' => [
    'api' => [
        'driver' => 'passport',
        'provider' => 'users',    // Central DB users
    ],
    'tenant' => [
        'driver' => 'passport',
        'provider' => 'tenant',   // Tenant DB users
    ],
],

'providers' => [
    'users' => [
        'driver' => 'eloquent',
        'model' => App\Models\User::class,  // Central
    ],
    'tenant' => [
        'driver' => 'eloquent',
        'model' => App\Models\Tenants\User::class,  // Tenant
    ],
],
```

---

## Testing

### Test Central Login

```bash
curl -X POST http://manager.prindustry.test/api/v2/in/mgr/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"admin@example.com","password":"password"}' \
  | jq .
```

**Check the database:**
```sql
SELECT id, user_id, tenant_id, expires_at
FROM oauth_access_tokens
ORDER BY created_at DESC
LIMIT 5;
```

You should see `tenant_id = NULL` for central users.

### Test Tenant Login

```bash
curl -X POST http://tenant1.prindustry.test/api/v1/mgr/login \
  -H "Content-Type: application/json" \
  -d '{"email":"user@tenant.com","password":"password"}' \
  | jq .
```

**Check the database:**
```sql
SELECT id, user_id, tenant_id, expires_at
FROM oauth_access_tokens
WHERE tenant_id IS NOT NULL
ORDER BY created_at DESC
LIMIT 5;
```

You should see `tenant_id = {tenant-uuid}` for tenant users.

### Test Token Scoping

Try using a tenant token on a different tenant's domain:

```bash
# Get token from tenant1
TOKEN=$(curl -X POST http://tenant1.prindustry.test/api/v1/mgr/login \
  -H "Content-Type: application/json" \
  -d '{"email":"user@tenant1.com","password":"password"}' \
  | jq -r '.data.access_token')

# Try to use it on tenant2 (should fail with 403)
curl -X GET http://tenant2.prindustry.test/api/v1/mgr/account/me \
  -H "Authorization: Bearer $TOKEN"
```

Expected response: `403 Forbidden - Invalid token for this tenant`

---

## Migration from Old Setup

If you previously had OAuth clients in each tenant database:

### 1. Run the migration

```bash
php artisan migrate:hyn-to-tenancy
php artisan migrate  # Adds tenant_id to Passport tables
```

### 2. Clean up tenant databases (optional)

The old `oauth_*` tables in tenant databases are no longer used. You can drop them:

```sql
-- Run for each tenant database
DROP TABLE IF EXISTS oauth_access_tokens;
DROP TABLE IF EXISTS oauth_auth_codes;
DROP TABLE IF EXISTS oauth_clients;
DROP TABLE IF EXISTS oauth_personal_access_clients;
DROP TABLE IF EXISTS oauth_refresh_tokens;
```

### 3. Users need to re-authenticate

All existing tokens will be invalid. Users need to log in again to get new tokens with `tenant_id`.

---

## Troubleshooting

### Problem: Login returns null/no token

**Check:**
1. OAuth clients exist in central DB:
   ```sql
   SELECT id, name, password_client FROM oauth_clients;
   ```

2. PASSWORD_CLIENT_ID/SECRET in .env match:
   ```bash
   grep PASSWORD_CLIENT .env
   ```

3. Passport migrations ran:
   ```sql
   SELECT id, tenant_id FROM oauth_access_tokens LIMIT 1;
   ```
   If `tenant_id` column doesn't exist → run migrations

### Problem: 403 "Invalid token for this tenant"

**Check:**
1. Token has correct tenant_id:
   ```sql
   SELECT id, user_id, tenant_id FROM oauth_access_tokens
   WHERE id = 'your-token-id';
   ```

2. Current tenant matches:
   ```php
   dd(tenant()->id);
   ```

3. Middleware is applied to routes:
   ```php
   Route::middleware(['auth:tenant', 'verify.tenant.token'])
   ```

### Problem: Central login broken

**Check:**
1. Central users should have `tenant_id = NULL`:
   ```sql
   SELECT * FROM oauth_access_tokens WHERE tenant_id IS NULL;
   ```

2. Using correct guard for central routes:
   ```php
   Route::middleware(['auth:api'])  // NOT 'auth:tenant'
   ```

### Problem: Tokens not getting tenant_id

**Check:**
1. Custom Token model is registered:
   ```php
   // In AuthServiceProvider
   Passport::useTokenModel(\App\Models\Passport\Token::class);
   ```

2. Tenant context is initialized before token creation:
   ```php
   dd(tenant());  // Should return tenant object, not null
   ```

---

## Files Reference

### New/Modified Files

```
app/
├── Models/
│   └── Passport/
│       ├── Token.php (NEW - adds tenant_id automatically)
│       └── AuthCode.php (NEW - adds tenant_id automatically)
├── Http/
│   └── Middleware/
│       └── VerifyTenantToken.php (NEW - validates token belongs to tenant)
├── Providers/
│   ├── AuthServiceProvider.php (MODIFIED - Universal Mode config)
│   └── TenantAuthServiceProvider.php (MODIFIED - removed tenant OAuth loading)
└── Http/
    └── Kernel.php (MODIFIED - added verify.tenant.token middleware)

database/
└── migrations/
    └── 2024_11_12_000001_add_tenant_id_to_passport_tables.php (NEW)
```

### Removed Files

```
app/
├── Console/
│   └── Commands/
│       └── TenantPassportInstall.php (DELETED - not needed in Universal Mode)
└── Models/
    └── Tenants/
        └── Passport/ (NOT DELETED but no longer used)
            ├── Token.php
            ├── Client.php
            ├── AuthCode.php
            ├── PersonalAccessClient.php
            └── RefreshToken.php
```

---

## Comparison: Before vs After

### Before (Tenant-Specific Clients)

```
❌ Each tenant database had oauth_clients table
❌ Had to run passport:install for each tenant
❌ Different client ID/secret per tenant
❌ TenantAuthServiceProvider loaded tenant-specific clients
❌ Tokens stored in tenant databases
❌ Complex setup, hard to manage
```

### After (Universal Mode)

```
✅ Single oauth_clients table in central DB
✅ One-time passport:install (central only)
✅ Same client ID/secret for all tenants
✅ Tokens stored in central DB with tenant_id
✅ Automatic tenant_id assignment
✅ Simple, clean, recommended by stancl/tenancy
```

---

## Best Practices

1. **Always use the central OAuth clients** - Don't create tenant-specific ones
2. **Apply verify.tenant.token middleware** to all authenticated tenant routes
3. **Monitor token usage** - Central storage makes this easy
4. **Revoke tokens centrally** when needed
5. **Use different token lifetimes** for central vs tenant if needed
6. **Test token scoping** - Ensure tenants can't use each other's tokens

---

## Additional Resources

- [stancl/tenancy Passport Integration](https://tenancyforlaravel.com/docs/v3/integrations/passport)
- [Laravel Passport Documentation](https://laravel.com/docs/passport)
- [Multi-Tenancy Best Practices](https://tenancyforlaravel.com/docs/v3/)

---

## Support

If authentication isn't working:

1. Check logs: `tail -f storage/logs/laravel.log`
2. Verify tenant_id column exists: `DESCRIBE oauth_access_tokens;`
3. Check TOKEN model is custom: `php artisan tinker` → `Passport::tokenModel()`
4. Test with debug mode: `APP_DEBUG=true`
5. Verify middleware order in Kernel.php

---

**Summary:** Universal Mode is simpler, cleaner, and the recommended approach for Passport + stancl/tenancy. All tenants share the same OAuth clients from the central database, with tokens scoped by `tenant_id`.
