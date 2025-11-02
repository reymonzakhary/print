<?php

namespace App\Models\Tenants\Media;

use App\Models\Traits\Slugable;
use Hyn\Tenancy\Traits\UsesTenantConnection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MediaSource extends Model
{
    use UsesTenantConnection, slugable;

    public $timestamps = false;

    protected $fillable = [
        'name', 'slug', 'ctx_id'
    ];

    /**
     * @return HasMany
     */
    public function rules()
    {
        return $this->hasMany(MediaSourceRule::class);
    }
}
