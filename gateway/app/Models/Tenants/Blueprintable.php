<?php

namespace App\Models\Tenants;

use Hyn\Tenancy\Traits\UsesTenantConnection;
use Illuminate\Database\Eloquent\Relations\MorphPivot;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Staudenmeir\LaravelAdjacencyList\Eloquent\HasRecursiveRelationships;

class Blueprintable extends MorphPivot
{
    use UsesTenantConnection, HasRecursiveRelationships;

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
