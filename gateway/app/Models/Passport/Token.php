<?php

namespace App\Models\Passport;

use Laravel\Passport\Token as PassportToken;

class Token extends PassportToken
{
    /**
     * The "booted" method of the model.
     *
     * This automatically adds the tenant_id when creating tokens for tenant users.
     */
    protected static function booted(): void
    {
        static::creating(function ($token) {
            // If we're in a tenant context, add the tenant_id
            if ($tenantId = tenant()?->id) {
                $token->tenant_id = $tenantId;
            }
        });

        static::retrieved(function ($token) {
            // When retrieving tokens, ensure we're in the right tenant context
            // This is handled by middleware, but we can add extra validation here if needed
        });
    }

    /**
     * Scope a query to only include tokens for the current tenant.
     */
    public function scopeForCurrentTenant($query)
    {
        if ($tenantId = tenant()?->id) {
            return $query->where('tenant_id', $tenantId);
        }

        return $query->whereNull('tenant_id');
    }
}
