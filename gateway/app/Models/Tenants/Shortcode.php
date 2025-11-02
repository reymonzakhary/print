<?php

namespace App\Models\Tenants;

use Hyn\Tenancy\Traits\UsesTenantConnection;
use Illuminate\Database\Eloquent\Model;

class Shortcode extends Model
{
    use UsesTenantConnection;

    protected $fillable = ['name', 'namespace', 'lexicon', 'value'];

}
