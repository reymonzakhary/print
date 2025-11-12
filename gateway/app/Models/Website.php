<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\AsArrayObject;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
class Website extends Model
{
    use HasFactory;

    protected $fillable = ['configure', 'supplier', 'external'];

    protected $casts = [
        'configure' => AsArrayObject::class,
    ];

    /**
     * Scope to find primary enabled entries
     *
     * @param $builder
     * @return void
     */
    public function scopeFindEnabledPrimary($builder): void
    {
        $builder->where([
            'supplier' => true
        ])->with(['hostnames' => function ($q) {
            $q->where('primary', true);
        }]);
    }

    /**
     * Scope to get enabled suppliers except the current one.
     *
     * This scope filters suppliers that are enabled and not the current one based on the tenant ID.
     * It also filters based on hostnames being primary and not starting with 'pr-'.
     *
     * @param Builder $builder
     * @return void
     */
    public function scopeGetEnabledSuppliersExceptMe($builder): void
    {
        $builder->where('supplier', true)
            ->where('id', '!=', request()->tenant->id)
            ->whereHas('hostnames', function ($q) {
                return $q->where('primary', true)
                    ->where('fqdn','NOT LIKE', 'pr-%');
            });
    }

    public function hostname()
    {
        return $this->hasMany(Hostname::class);
    }
}
