<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Module extends Model
{
    //


    protected $table = 'modules';
    protected $fillable = [
        'name', 'path', 'alias', 'description', 'keywords', 'is_active', 'order', 'providers', 'aliases', 'files', 'requires'
    ];

    /**
     * @return BelongsToMany
     */

    public function domains()
    {
        return $this->belongsToMany(Domain::class, "hostname_modules");
    }
}
