<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\AsArrayObject;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Legacy Website model - kept for backward compatibility
 * This is now an alias/proxy for the Tenant model
 * @deprecated Use Tenant model instead
 */
class Website extends Model
{
    use HasFactory;

    protected $table = 'tenants';

    protected $fillable = ['configure', 'supplier', 'external', 'email', 'data'];

    protected $casts = [
        'configure' => 'array',
        'supplier' => 'array',
        'external' => 'array',
        'data' => 'array',
    ];

    /**
     * Scope to find primary enabled entries
     *
     * @param $builder
     * @return void
     */
    public function scopeFindEnabledPrimary($builder): void
    {
        $builder->where('supplier->enabled', true)
            ->with(['domains' => function ($q) {
                $q->where('is_primary', true);
            }]);
    }

    /**
     * Scope to get enabled suppliers except the current one.
     *
     * This scope filters suppliers that are enabled and not the current one based on the tenant ID.
     * It also filters based on domains being primary and not starting with 'pr-'.
     *
     * @param Builder $builder
     * @return void
     */
    public function scopeGetEnabledSuppliersExceptMe($builder): void
    {
        $builder->where('supplier->enabled', true)
            ->where('id', '!=', request()->tenant?->id)
            ->whereHas('domains', function ($q) {
                return $q->where('is_primary', true)
                    ->where('domain','NOT LIKE', 'pr-%');
            });
    }

    /**
     * @deprecated Use domains() instead
     */
    public function hostnames()
    {
        return $this->domains();
    }

    public function domains()
    {
        return $this->hasMany(Domain::class, 'tenant_id');
    }

    public function hostname()
    {
        return $this->hasOne(Domain::class, 'tenant_id')->where('is_primary', true);
    }

    /**
     * Get UUID attribute for backward compatibility
     */
    public function getUuidAttribute()
    {
        return $this->id;
    }
}
