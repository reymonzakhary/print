<?php

namespace App\Models\Tenants;

use App\Foundation\FileManager\Traits\InteractWithMedia;
use App\Models\Traits\CanBeScoped;
use App\Models\Traits\HasChildren;
use App\Models\Traits\ResolveLanguageRouteBinding;
use App\Models\Traits\Slugable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\EloquentSortable\Sortable;
use Spatie\EloquentSortable\SortableTrait;
use Staudenmeir\LaravelAdjacencyList\Eloquent\HasRecursiveRelationships;

class Category extends Model implements Sortable
{
    use Slugable, SortableTrait, HasChildren,
        HasRecursiveRelationships, HasFactory, ResolveLanguageRouteBinding,
        CanBeScoped, InteractWithMedia;

    /**
     * @var string[]
     */
    protected $fillable = [
        'name', 'slug', 'iso', 'description', 'created_by', 'parent_id',
        'published', 'published_by', 'published_at', 'sort', 'row_id', 'base_id',
        'margin_value', 'margin_type', 'discount_type', 'discount_value'
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
     * @return HasMany
     */
    public function children()
    {
        return $this->hasMany(__CLASS__, 'parent_id', 'row_id')
            ->where('iso', app()->getLocale());
    }

    /**
     * @return bool
     */
    public function hasProducts(): bool
    {
        return (bool)$this->products()->count() > 0;
    }

    /**
     * @return HasMany
     */
    public function products(): HasMany
    {
        return $this->hasMany(Product::class, 'category_id', 'row_id')
            ->where('iso', app()->getLocale());
    }

    public function removeMedia(): self
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
            ->addMediaCollection('custom-category')
            ->acceptsMimeTypes(['image/jpeg'])
            ->useDisk('assets');
    }
}
