<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;

class Language extends Model
{


    protected $fillable = [
        'name', 'iso'
    ];
}
