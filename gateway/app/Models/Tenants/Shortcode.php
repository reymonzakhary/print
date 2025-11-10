<?php

namespace App\Models\Tenants;

use Illuminate\Database\Eloquent\Model;

class Shortcode extends Model
{


    protected $fillable = ['name', 'namespace', 'lexicon', 'value'];

}
