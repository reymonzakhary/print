<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;

class Stock extends Model
{


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
