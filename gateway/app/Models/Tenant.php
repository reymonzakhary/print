<?php

namespace App\Models;

use App\Exceptions\NoPrimaryDomainException;
use App\Models\Traits\HashPassword;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Cashier\Billable;
use Stancl\Tenancy\Contracts\TenantWithDatabase;
use Stancl\Tenancy\Database\Concerns\HasDatabase;
use Stancl\Tenancy\Database\Concerns\HasDomains;
use Stancl\Tenancy\Database\Models\Tenant as BaseTenant;

class Tenant extends BaseTenant implements TenantWithDatabase
{
    use HasFactory, HasDatabase, HasDomains, Billable, HashPassword;

    protected $fillable = [
        'email',
        'data',
        'supplier',
        'external',
        'configure',
        'stripe_id',
        'pm_type',
        'pm_last_four',
        'trial_ends_at',
    ];

    protected $casts = [
        'trial_ends_at' => 'datetime',
        'data' => 'array', // This is crucial for handling JSON data
        'configure' => 'array', // If this is also JSON
        'supplier' => 'array',  // If this is also JSON
        'external' => 'array',  // If this is also JSON
    ];


    public static function getCustomColumns(): array
    {
        return [
            'id',
            'email',
            'supplier',
            'external',
            'configure',
            'stripe_id',
            'pm_type',
            'pm_last_four',
            'trial_ends_at',
        ];
    }

    public function primary_domain()
    {
        return $this->hasOne(Domain::class)->where('is_primary', true);
    }

    public function fallback_domain()
    {
        return $this->hasOne(Domain::class)->where('is_fallback', true);
    }

    /**
     * @throws NoPrimaryDomainException
     */
    public function route($route, $parameters = [], $absolute = true)
    {
        if (! $this->primary_domain) {
            throw new NoPrimaryDomainException;
        }

        $domain = $this->primary_domain->domain;

        $parts = explode('.', $domain);
        if (count($parts) === 1) { // If subdomain
            $domain = Domain::domainFromSubdomain($domain);
        }

        return tenant_route($domain, $route, $parameters, $absolute);
    }

    public function impersonationUrl($user_id): string
    {
        $token = tenancy()->impersonate($this, $user_id, $this->route('tenant.home'), 'tenants')->token;

        return $this->route('tenant.impersonate', ['token' => $token]);
    }

    /**
     * Get the tenant's subscription plan name.
     */
    public function getPlanNameAttribute(): string
    {
        return config('saas.plans')[$this->subscription('default')->stripe_price];
    }

    /**
     * Is the tenant actively subscribed (not on grace period).
     */
    public function getOnActiveSubscriptionAttribute(): bool
    {
        return $this->subscribed('default') && ! $this->subscription('default')->canceled();
    }

    /**
     * Can the tenant use the application (is on trial or subscription).
     */
    public function getCanUseAppAttribute(): bool
    {
        return $this->onTrial() || $this->subscribed('default');
    }
}
