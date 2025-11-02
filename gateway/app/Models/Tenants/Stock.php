<?php

namespace App\Models\Tenants;

use Hyn\Tenancy\Traits\UsesTenantConnection;
use Illuminate\Database\Eloquent\Model;

class Stock extends Model
{
    use UsesTenantConnection;

    protected $fillable = [
        'qty',
        'location_id',
        'sku_id'
    ];

    public function location()
    {
        return $this->belongsTo(Location::class);
    }

    public function sku()
    {
        return $this->belongsTo(Sku::class);
    }
}
