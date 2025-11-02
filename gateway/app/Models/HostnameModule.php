<?php

namespace App\Models;

use Hyn\Tenancy\Traits\UsesSystemConnection;
use Illuminate\Database\Eloquent\Model;

class HostnameModule extends Model
{
    //
    use UsesSystemConnection;

    protected $table = 'hostname_modules';
}
