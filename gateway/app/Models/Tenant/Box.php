<?php

namespace App\Models\Tenant;

use App\Foundation\FileManager\Traits\InteractWithMedia;
use App\Models\Traits\GenerateIdentifier;
use App\Models\Traits\HasChildren;
use App\Models\Traits\ResolveLanguageRouteBinding;
use App\Models\Traits\Slugable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\EloquentSortable\Sortable;
use Spatie\EloquentSortable\SortableTrait;
use Staudenmeir\LaravelAdjacencyList\Eloquent\HasRecursiveRelationships;


class Box extends Model implements Sortable
{
    use GenerateIdentifier,
        Slugable, SortableTrait, HasRecursiveRelationships, HasChildren, ResolveLanguageRouteBinding, InteractWithMedia;

    /**
     * @var string[]
     */
    protected $fillable = [
        'name', 'slug', 'description', 'input_type', 'incremental', 'select_limit', 'option_limit', 'parent_id', 'sqm', 'sort',
        'iso', 'base_id', 'row_id', 'created_by', 'appendage'
    ];

    /**
     * sorting tables column
     * @var array
     */
    public $sortable = [
        'order_column_name' => 'sort',
        'sort_when_creating' => true,
    ];


    /**
     * Get the name of the local key column.
     *
     * @return string
     */
    public function getLocalKeyName()
    {
        return 'row_id';
    }

    /**
     * @return string[][]
     */
    public function getCustomPaths()
    {
        return [
            [
                'name' => 'slug_path',
                'column' => 'slug',
                'separator' => '/',
            ],
        ];
    }

    /**
     * overriding package method
     * @return HasMany
     */
    public function children()
    {
        return $this->hasMany(static::class, 'parent_id', 'row_id')
            ->where('iso', app()->getLocale());
    }


    /**
     * @return HasMany
     */
    public function variations()
    {
        return $this->hasMany(Variation::class, 'box_id', 'id');
    }

    /**
     * @return HasMany
     */
    public function options()
    {
        return $this->hasMany(Option::class, 'box_id', 'row_id')
            ->where('iso', app()->getLocale())
            ->whereNull('parent_id');
    }

    /**
     * @return bool
     */
    public function hasOptions(): bool
    {
        return (bool)$this->options()->count() > 0;
    }

    public function removeMeida(): self
    {
        $this->media()->detach();
        return $this;
    }

    /**
     *
     */
    public function registerMediaCollections(): void
    {
        $this
            ->addMediaCollection('custom-box')
            ->acceptsMimeTypes(['image/jpeg'])
            ->useDisk('assets');
    }

    protected static function booted()
    {
        static::addGlobalScope('iso', function (Builder $builder) {
            $builder->where('iso', app()->getLocale());
        });
    }
}
