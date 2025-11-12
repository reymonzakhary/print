<?php

namespace App\Models\Passport;

use Laravel\Passport\AuthCode as PassportAuthCode;

class AuthCode extends PassportAuthCode
{
    /**
     * The "booted" method of the model.
     *
     * This automatically adds the tenant_id when creating auth codes for tenant users.
     */
    protected static function booted(): void
    {
        static::creating(function ($authCode) {
            // If we're in a tenant context, add the tenant_id
            if ($tenantId = tenant()?->id) {
                $authCode->tenant_id = $tenantId;
            }
        });
    }

    /**
     * Scope a query to only include auth codes for the current tenant.
     */
    public function scopeForCurrentTenant($query)
    {
        if ($tenantId = tenant()?->id) {
            return $query->where('tenant_id', $tenantId);
        }

        return $query->whereNull('tenant_id');
    }
}
