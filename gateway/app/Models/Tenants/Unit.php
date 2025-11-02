<?php

namespace App\Models\Tenants;

use Hyn\Tenancy\Traits\UsesTenantConnection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Unit extends Model
{
    use HasFactory, UsesTenantConnection;

    protected $fillable = ['name', 'short_name', 'operator', 'operator_value'];
}
