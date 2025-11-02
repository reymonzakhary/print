<?php

namespace App\Models;

use Hyn\Tenancy\Traits\UsesSystemConnection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Module extends Model
{
    //
    use UsesSystemConnection;

    protected $table = 'modules';
    protected $fillable = [
        'name', 'path', 'alias', 'description', 'keywords', 'is_active', 'order', 'providers', 'aliases', 'files', 'requires'
    ];

    /**
     * @return BelongsToMany
     */

    public function hostnames()
    {
        return $this->belongsToMany(Hostname::class, "hostname_modules");
    }
}
