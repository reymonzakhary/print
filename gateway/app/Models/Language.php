<?php

namespace App\Models;

use Hyn\Tenancy\Traits\UsesSystemConnection;
use Illuminate\Database\Eloquent\Model;

class Language extends Model
{
    use UsesSystemConnection;

    protected $fillable = [
        'name', 'iso'
    ];
}
