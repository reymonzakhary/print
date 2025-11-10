<?php

namespace Modules\Campaign\Entities;

use App\Models\Tenant\DesignProviderTemplate;
use App\Models\Tenant\User;
use App\Models\Traits\InteractsWithMedia;
use App\Models\Traits\Slugable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use JsonException;

class Campaign extends Model
{
    use Slugable, InteractsWithMedia;

    protected $fillable = [
        'name', 'description', 'start_on', 'end_on', 'fm_id', 'config',
        'active', 'assets', 'created_by', 'updated_by', 'locked_by'
    ];

    protected $casts = [
        'start_on' => 'date'
    ];

    /**
     * @param $value
     * @return mixed
     * @throws JsonException
     */
    public function getConfigAttribute($value)
    {
        return json_decode($value, true);
    }

    /**
     * @param $value
     * @return string
     * @throws JsonException
     */
    public function setConfigAttribute($value)
    {
        return $this->attributes['config'] = json_encode($value, JSON_THROW_ON_ERROR);
    }

    /**
     * @return BelongsTo
     */
    public function createdBy()
    {
        return $this->belongsTo(User::class, 'id', 'created_by');
    }

    /**
     * @return BelongsTo
     */
    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'id', 'updated_by');
    }

    /**
     * @return BelongsTo
     */
    public function lockedBy()
    {
        return $this->belongsTo(User::class, 'locked_by', 'id');
    }

    /**
     * @return object
     */
    final public function getFileAttribute(): ?object
    {
        return collect($this->getMedia('campaigns'))->first();
    }

    /**
     * @return BelongsToMany
     */
    public function providerTemplates(): BelongsToMany
    {
        return $this->belongsToMany(DesignProviderTemplate::class, 'campaign_provider_templates')
            ->withPivot('assets')->withTimestamps();
    }

    /**
     * @return HasMany
     */
    public function exports()
    {
        return $this->hasMany(CampaignExport::class)->orderBy('created_at', 'DESC');
    }
}
