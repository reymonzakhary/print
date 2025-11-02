<?php

namespace App\Models\Tenants;

use Hyn\Tenancy\Traits\UsesTenantConnection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Location extends Model
{
    use HasFactory, UsesTenantConnection;

    protected $fillable = ['warehouse_id', 'sort', 'ean', 'position'];

    public function warehouse()
    {
        $this->belongsTo(Warehouse::class);
    }

    public function setPositionAttribute($value)
    {
        $this->attributes['position'] = implode(',', $value);
    }

    public function getPositionAttribute($value)
    {
        return explode(',', $value);
    }
}
