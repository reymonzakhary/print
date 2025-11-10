<?php

namespace App\Models\Tenants;

use App\Foundation\FileManager\Traits\InteractWithMedia;
use App\Models\Traits\CanBeScoped;
use App\Models\Traits\HasPrice;
use App\Models\Traits\ResolveLanguageRouteBinding;
use App\Models\Traits\Slugable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\AsArrayObject;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\EloquentSortable\Sortable;
use Spatie\EloquentSortable\SortableTrait;
use Staudenmeir\LaravelAdjacencyList\Eloquent\HasRecursiveRelationships;

class Option extends Model implements Sortable
{
    use CanBeScoped,
        Slugable, HasPrice, SortableTrait,
        HasRecursiveRelationships,
        ResolveLanguageRouteBinding, InteractWithMedia;

    protected $fillable = [
        'name', 'description', 'slug', 'box_id', 'input_type', 'incremental_by', 'min', 'max', 'width', 'height', 'length', 'unit',
        'margin_value', 'margin_type', 'price', 'discount_type', 'discount_value',
        'price_switch', 'sort', 'secure', 'parent_id', 'iso', 'base_id', 'row_id',
        'created_by', 'single', 'upto', 'properties'
    ];
    protected $casts = [
        'properties' => AsArrayObject::class
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
     * @return BelongsTo
     */
    public function box()
    {
        return $this->belongsTo(Box::class, 'box_id', 'row_id');
    }

    /**
     * @return self
     */
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
            ->addMediaCollection('custom-option')
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
