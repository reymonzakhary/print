<?php

namespace App\Models\Tenants;

use App\Foundation\FileManager\Traits\InteractWithMedia;
use App\Models\Traits\CanBeScoped;
use App\Models\Traits\Slugable;
use Hyn\Tenancy\Traits\UsesTenantConnection;
use Illuminate\Database\Eloquent\Casts\AsArrayObject;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Spatie\EloquentSortable\Sortable;
use Spatie\EloquentSortable\SortableTrait;

class Product extends Model implements Sortable
{
    use UsesTenantConnection, Slugable, SortableTrait,
        CanBeScoped, InteractWithMedia;

    /**
     * add the relation name for securing the key in db
     * @var string
     */
    protected string $relation;

    public function resolveRouteBinding($value, $field = null)
    {
        return $this->where(['row_id' => $value, 'iso' => app()->getLocale()])->first()
            ?? abort(404, __('Not Found -- There is no Product found'));
    }

    /**
     * @var $fillable []
     */
    protected $fillable = [
        'name', 'slug', 'description', 'art_num', 'sort',
        'margin_value', 'margin_type', 'discount_type', 'discount_value',
        'free', 'properties', 'sale_start_at', 'sale_end_at', 'stock_product',
        'vat_id', 'unit_id', 'variation', 'combination',
        'brand_id', 'category_id', 'iso', 'row_id', 'published', 'created_by', 'published_by', 'published_at',
        'expire_date', 'expire_after', 'excludes'
    ];

    /**
     * @var string[]
     */
    protected $casts = [
        'properties' => AsArrayObject::class,
        'sale_start_at' => 'date',
        'sale_end_at' => 'date',
        'expire_date' => 'date'
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
     * @return string
     */
    public function getRouteKeyName()
    {
        if (preg_match('/^cart+/', request()->route()?->getName(), $matches)) {
            return 'row_id';
        }
        return 'row_id';
    }

    /**
     * @return hasOne
     */
    public function sku(): hasOne
    {
        return $this->hasOne(Sku::class, 'product_id', 'row_id');
    }

    /**
     * @return hasMany
     */
    public function skus(): hasMany
    {
        return $this->hasMany(Sku::class, 'product_id', 'row_id');
    }

    /**
     * @return BelongsTo
     */
    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class, 'row_id', 'brand_id');
    }

    /**
     * @return BelongsTo
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'category_id', 'row_id')->where('iso', app()->getLocale());
    }

    public function getHasVariationsAttribute(): bool
    {
        return (bool)$this->variations()->count() > 0;
    }

    /**
     */
    public function variations()
    {
        return $this->hasMany(Variation::class, 'product_id', 'row_id');
    }

    /**
     * @return HasMany
     */
    public function appendage()
    {
        return $this->hasMany(Variation::class, 'product_id', 'row_id')
            ->where('appendage', '=', true);
    }

    /**
     * @param $value
     * @return bool|null
     */
    public function setFreeAttribute($value): ?bool
    {
        if ($this->variations()->count() === 0) {
            return $this->attributes['free'] = $this->sku?->price === 0;
        }

        return $this->attributes['free'] = false;
    }

    /**
     * @return bool
     */
    public function deleteWithAllRelations(): bool
    {
        $this->sku?->stocks()?->delete();
        $this->sku()->delete();
        $this->detachMedia();
        return $this->where('row_id', '=', $this->id)->get()->map(function ($row) {
            collect($row->variations)->map(function ($variation) {
                $variation?->sku()->first()?->stocks()?->delete();
                $variation?->sku()->delete();
                $variation?->media()->delete();
                $variation?->delete();
                return true;
            });
            return $row->delete();
        })->first();
    }

    /**
     * @return mixed
     */
    public function deleteWithVariations()
    {
        return $this->where('row_id', '=', $this->id)->get()->map(function ($row) {
            collect($row->variations)->map(function ($variation) {
                $variation?->sku()->first()?->stocks()?->delete();
                $variation?->sku()->delete();
                $variation?->delete();
                return true;
            });
        });
    }

    public function registerMediaCollections(): void
    {
        $this
            ->addMediaCollection('custom-product')
            ->acceptsMimeTypes(['image/jpeg', 'application/pdf'])
            ->useDisk('assets');
    }

    public function blueprints()
    {
        return $this->morphToMany(
            Blueprint::class,
            'blueprintable',
            'blueprintables',
            'blueprintable_id',
            'blueprint_id',
            'row_id',
        )
            ->using(Blueprintable::class)
            ->withPivot('step', 'queueable');
    }
}
