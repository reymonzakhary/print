<?php

namespace App\Models\Tenants;

use Hyn\Tenancy\Traits\UsesTenantConnection;
use Illuminate\Database\Eloquent\Model;

class Language extends Model
{
    use UsesTenantConnection;

    protected $fillable = [
        'name', 'iso'
    ];
}
