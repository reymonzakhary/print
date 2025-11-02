<?php

namespace App\Models\Tenants;

use Hyn\Tenancy\Traits\UsesTenantConnection;
use Illuminate\Database\Eloquent\Model;

class Type extends Model
{
    use UsesTenantConnection;

    protected $fillable = [
        'name', 'slug', 'action', 'ns'
    ];
}
