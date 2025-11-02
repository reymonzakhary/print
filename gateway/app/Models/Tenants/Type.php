<?php

namespace App\Models\Tenants;

use Illuminate\Database\Eloquent\Model;

class Type extends Model
{


    protected $fillable = [
        'name', 'slug', 'action', 'ns'
    ];
}
