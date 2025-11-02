<?php

namespace App\Models\Tenants;

use Illuminate\Database\Eloquent\Relations\MorphPivot;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Staudenmeir\LaravelAdjacencyList\Eloquent\HasRecursiveRelationships;

class Blueprintable extends MorphPivot
{
    use HasRecursiveRelationships;

    /**
     * @var bool
     */
    public $timestamps = false;

    /**
     * @var string[]
     */
    protected $fillable = [
        'step', 'queueable'
    ];

    /**
     * @return MorphTo
     */
    public function blueprintable()
    {
        return $this->morphTo();
    }
}
