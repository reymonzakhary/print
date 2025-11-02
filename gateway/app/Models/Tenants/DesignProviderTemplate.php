<?php

namespace App\Models\Tenants;

use App\Models\Traits\CanBeScoped;
use App\Models\Traits\InteractsWithMedia;
use Hyn\Tenancy\Traits\UsesTenantConnection;
use Illuminate\Database\Eloquent\Casts\AsArrayObject;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\EloquentSortable\SortableTrait;

class DesignProviderTemplate extends Model
{
    use UsesTenantConnection, CanBeScoped, InteractsWithMedia, SortableTrait;

    /**
     * add the relation name for securing the key in db
     * @var string|null
     */
    protected string $relation;

    protected $casts = [
        'content' => 'string',
        'properties' => AsArrayObject::class,
        'settings' => AsArrayObject::class,
    ];

    public $sortable = [
        'order_column_name' => 'sort',
        'sort_when_creating' => true,
    ];

    protected $fillable = [
        'name', 'description', 'settings', 'active', 'design_provider_id',
        'folder', 'icon', 'content', 'locked', 'locked_by', 'properties', 'static',
        'path', 'created_by',
    ];

    /**
     * @return BelongsTo
     */
    public function designProvider(): BelongsTo
    {
        return $this->belongsTo(DesignProvider::class, 'design_provider_id', 'id');
    }

    /**
     * @param $value
     * @return array
     */
    public function getAssetsAttribute($value)
    {
        return $this->getMedia('design-provider-templates');
    }

    /**
     * @return BelongsTo
     */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return BelongsTo
     */
    public function lockedBy(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function registerMediaCollections(): void
    {
        $this
            ->addMediaCollection('design-provider-templates')
            ->acceptsMimeTypes(['image/jpeg', 'application/pdf'])
            ->useDisk('tenancy');
    }
}
