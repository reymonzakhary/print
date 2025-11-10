<?php

namespace App\Models\Tenant\Media;

use App\Models\Traits\Slugable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MediaSource extends Model
{
    use slugable;

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
