<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;

class Shortcode extends Model
{


    protected $fillable = ['name', 'namespace', 'lexicon', 'value'];

}
