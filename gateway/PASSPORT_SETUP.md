# Laravel Passport Dual-Level Authentication Setup

This document explains how Passport authentication works in this multi-tenant application and how to set it up correctly.

## Overview

This application uses **Laravel Passport** for API authentication on **two separate levels**:

1. **Central Database** - Manager authentication (`manager.prindustry.test`)
2. **Tenant Databases** - Tenant user authentication (`{subdomain}.prindustry.test`)

## Authentication Endpoints

### Central Manager Login
- **URL**: `http://manager.prindustry.test/api/v2/in/mgr/auth/login`
- **Method**: POST
- **Database**: Central (cec)
- **User Model**: `App\Models\User`
- **Guard**: `api` (Passport)

```bash
curl -X POST http://manager.prindustry.test/api/v2/in/mgr/auth/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "admin@example.com",
    "password": "password"
  }'
```

### Tenant User Login
- **URL**: `http://{subdomain}.prindustry.test/api/v1/mgr/login`
- **Method**: POST
- **Database**: Tenant-specific (tenant_{uuid})
- **User Model**: `App\Models\Tenants\User`
- **Guard**: `tenant` (Passport)

```bash
curl -X POST http://tenant1.prindustry.test/api/v1/mgr/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "user@tenant.com",
    "password": "password"
  }'
```

## Initial Setup

### 1. Central Database Setup

Run Passport install for the central database:

```bash
php artisan migrate
php artisan passport:install
```

This will:
- Create `oauth_*` tables in the central database
- Generate encryption keys
- Create password grant and personal access clients

**Important**: Save the `Client ID` and `Client Secret` from the output to your `.env` file:

```env
PASSWORD_CLIENT_ID=your-client-id-here
PASSWORD_CLIENT_SECRET=your-client-secret-here
```

### 2. Tenant Databases Setup

After migrating from Hyn or creating new tenants, you MUST install Passport OAuth clients for each tenant database:

```bash
php artisan tenants:passport-install
```

This command will:
- Iterate through all tenant databases
- Create a password grant OAuth client for each tenant
- Create a personal access client for each tenant
- Skip tenants that already have clients (use `--force` to reinstall)

**Why is this needed?**

Each tenant database has its own `oauth_clients` table. When a tenant user tries to log in:

1. The request hits the tenant's domain (e.g., `tenant1.prindustry.test`)
2. Middleware identifies the tenant and switches to their database
3. `TenantAuthServiceProvider` loads the OAuth client from the tenant's database
4. `ProxyRequest` makes an internal call to `/oauth/token` using the tenant's client ID/secret
5. Passport validates the credentials against the tenant's `oauth_clients` table
6. If no client exists, authentication fails and returns `null`

## Troubleshooting

### Problem: Tenant login returns null / no token

**Symptom**: Central login works, but tenant login returns no access token.

**Cause**: The tenant database doesn't have OAuth clients set up.

**Solution**:
```bash
php artisan tenants:passport-install
```

Check the Laravel logs for warnings:
```bash
tail -f storage/logs/laravel.log
```

Look for: `No password grant client found for tenant`

### Problem: Client ID mismatch

**Symptom**: Error about invalid client credentials.

**Cause**: The `PASSWORD_CLIENT_ID` in `.env` doesn't match any client in the tenant database.

**Solution**: Each tenant has its own client ID. The `TenantAuthServiceProvider` automatically loads the correct client ID when a tenant context is initialized. Make sure:

1. The tenant database has OAuth clients: `php artisan tenants:passport-install`
2. The `TenantAuthServiceProvider` is being loaded (check `config/app.php`)
3. Debug logging is enabled: `APP_DEBUG=true`

### Problem: After migration, can't login

**Cause**: You migrated tenants from Hyn but didn't set up OAuth clients.

**Solution**:
```bash
# Run the Passport install command
php artisan tenants:passport-install

# Verify migrations ran
php artisan tenants:verify-migrations
```

## How It Works

### Central Authentication Flow

1. Request → `manager.prindustry.test/api/v2/in/mgr/auth/login`
2. `AuthServiceProvider` has already loaded central Passport clients
3. `ProxyRequest` creates internal request to `/oauth/token`
4. Uses `PASSWORD_CLIENT_ID` and `PASSWORD_CLIENT_SECRET` from `.env`
5. Passport queries central DB `oauth_clients` table
6. Returns access_token + refresh_token

### Tenant Authentication Flow

1. Request → `{subdomain}.prindustry.test/api/v1/mgr/login`
2. `InitializeTenancyByDomain` middleware identifies tenant
3. `SwitchConnectionServiceProvider` switches to tenant database
4. `TenantAuthServiceProvider::boot()` runs and loads tenant's OAuth client:
   ```php
   $passwordClient = Client::where('password_client', true)->first();
   Config::set('services.passport.password_client_id', $passwordClient->id);
   Config::set('services.passport.password_client_secret', $passwordClient->secret);
   ```
5. `ProxyRequest` creates internal request to `/oauth/token`
6. Uses the tenant's client ID/secret (dynamically loaded above)
7. Passport queries tenant DB `oauth_clients` table
8. Returns access_token + refresh_token for tenant user

## Commands Reference

### `php artisan tenants:passport-install`

Installs Passport OAuth clients for all tenant databases.

**Options**:
- `--force` - Reinstall even if clients already exist

**Example**:
```bash
# Install for all tenants
php artisan tenants:passport-install

# Force reinstall
php artisan tenants:passport-install --force
```

### `php artisan migrate:hyn-to-tenancy`

Migrates tenants from Hyn to stancl/tenancy. After running this, you MUST run `tenants:passport-install`.

### `php artisan tenants:verify-migrations`

Verifies that all tenant databases have migrations run properly.

**Options**:
- `--fix` - Run migrations on tenant databases that are missing tables

## File Structure

### Configuration Files

- `config/auth.php` - Guard definitions (`api` and `tenant`)
- `config/passport.php` - Passport settings
- `config/services.php` - OAuth client ID/secret config
- `.env` - Central database client credentials

### Providers

- `app/Providers/AuthServiceProvider.php` - Central Passport setup
- `app/Providers/TenantAuthServiceProvider.php` - Tenant Passport setup (dynamically loads clients)
- `app/Providers/RouteServiceProvider.php` - Route domain binding

### Controllers

- `app/Http/Controllers/System/V2/Mgr/Auth/AuthenticationController.php` - Central login
- `app/Http/Controllers/Tenant/Mgr/Auth/AuthenticationController.php` - Tenant login

### Utilities

- `app/Utilities/ProxyRequest.php` - OAuth token proxy (used by both central and tenant auth)

### Commands

- `app/Console/Commands/TenantPassportInstall.php` - Install Passport for tenants
- `app/Console/Commands/MigrateFromHynToTenancy.php` - Migrate from Hyn to stancl/tenancy

## Environment Variables

Required in `.env`:

```env
# Central Database OAuth Client (for manager authentication)
PASSWORD_CLIENT_ID=93c26c96-694c-4fae-8324-a1640b756394
PASSWORD_CLIENT_SECRET=ePT5FYIFxQ5OA5kvBAUH62oBfmoG63QUUf1VgwmC

# Tenant Configuration
TENANT_URL_BASE=prindustry.test
TENANCY_DEFAULT_CONNECTION=system

# Debug (helpful for troubleshooting auth issues)
APP_DEBUG=true
```

## Testing Authentication

### Test Central Login

```bash
curl -X POST http://manager.prindustry.test/api/v2/in/mgr/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"admin@example.com","password":"password"}' \
  | jq .
```

Expected response:
```json
{
  "data": {
    "token_type": "Bearer",
    "expires_in": 432000,
    "access_token": "eyJ0eXAiOiJKV1...",
    "refresh_token": "def50200db4497..."
  },
  "message": "You have logged in successfully",
  "status": 200
}
```

### Test Tenant Login

```bash
curl -X POST http://tenant1.prindustry.test/api/v1/mgr/login \
  -H "Content-Type: application/json" \
  -d '{"email":"user@tenant.com","password":"password"}' \
  | jq .
```

Expected response:
```json
{
  "data": {
    "token_type": "Bearer",
    "expires_in": 432000,
    "access_token": "eyJ0eXAiOiJKV1...",
    "refresh_token": "def50200db4497..."
  },
  "message": "You have been logged in successfully",
  "status": 200
}
```

### If Login Returns `null`

Check logs:
```bash
tail -f storage/logs/laravel.log | grep -i "oauth\|passport\|client"
```

Run the install command:
```bash
php artisan tenants:passport-install -v
```

## Migration Checklist

When migrating from Hyn to stancl/tenancy:

- [ ] Run `php artisan migrate:hyn-to-tenancy`
- [ ] Run `php artisan tenants:passport-install`
- [ ] Run `php artisan tenants:verify-migrations`
- [ ] Test central login at `manager.prindustry.test/api/v2/in/mgr/auth/login`
- [ ] Test tenant login at `{subdomain}.prindustry.test/api/v1/mgr/login`
- [ ] Verify tokens work with authenticated endpoints
- [ ] Check logs for any OAuth errors

## Additional Notes

- Each tenant has its own OAuth client with a unique ID and secret
- The `TenantAuthServiceProvider` dynamically loads the correct client based on tenant context
- Passport tokens are stored in the respective database (central or tenant)
- Token expiration is set in the providers:
  - Central: 30 minutes (access), 10 days (refresh)
  - Tenant: 5 days (access), 10 minutes (refresh) - Note: This seems backwards, consider adjusting
- OAuth clients use UUIDs as IDs (`'client_uuids' => true` in `config/passport.php`)

## Support

If you encounter issues:

1. Enable debug mode: `APP_DEBUG=true`
2. Check logs: `tail -f storage/logs/laravel.log`
3. Verify tenant has OAuth clients: Check the `oauth_clients` table in the tenant database
4. Run passport install: `php artisan tenants:passport-install --force`
5. Check middleware order in `app/Http/Kernel.php`
