<?php

namespace App\Models\Tenants;

use Illuminate\Database\Eloquent\Relations\MorphPivot;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Accessable extends MorphPivot
{


    protected $table = 'accessables';
    /**
     * @var bool
     */
    public $timestamps = false;

    protected $fillable = [
        'team_id', 'accessable_type', 'accessable_id'
    ];

    /**
     * @return MorphTo
     */
    public function accessable()
    {
        return $this->morphTo();
    }

}
