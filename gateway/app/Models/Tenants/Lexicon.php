<?php

namespace App\Models\Tenants;

use App\Models\Traits\CanBeScoped;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

final class Lexicon extends Model
{

    use CanBeScoped;

    /**
     * Specifies the attributes that are mass assignable.
     *
     * @var array
     */
    public $fillable = [
        'name', 'template', 'namespace', 'language', 'value', 'area'
    ];
}
