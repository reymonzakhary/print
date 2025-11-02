<?php

namespace App\Models\Tenants;

use App\Foundation\FileManager\Traits\InteractWithMedia;
use App\Models\Traits\HasPrice;
use Hyn\Tenancy\Traits\UsesTenantConnection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Support\Str;
use Spatie\EloquentSortable\Sortable;
use Spatie\EloquentSortable\SortableTrait;
use Staudenmeir\LaravelAdjacencyList\Eloquent\HasRecursiveRelationships;

class Sku extends Model implements Sortable
{
    use UsesTenantConnection, HasPrice, InteractWithMedia, SortableTrait, HasRecursiveRelationships;

//    protected $keyType = 'string';

    protected $fillable = [
        'ean', 'product_id', 'price', 'low_qty_threshold', 'option_id',
        'sku', 'open_stock', 'parent_id', 'published', 'high_qty_threshold', 'sort', 'sale_start_at', 'sale_end_at'
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
     * @var string[]
     */
    protected $casts = [
        'open_stock' => 'date',
        'sale_end_at' => 'date',
        'sale_start_at' => 'date'
    ];

    /**
     * @return BelongsTo
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id', 'row_id')
            ->where('iso', app()->getLocale());
    }

    /**
     * @return HasMany
     */
    public function variations(): HasMany
    {
        return $this->hasMany(Variation::class, 'sku', 'sku');
    }

    /**
     * @return HasManyThrough
     */
    public function childrens()
    {
        return $this
            ->hasManyThrough(Product::class, __CLASS__, 'parent_id', 'row_id', 'id', 'product_id')
            ->where('iso', app()->getLocale());
    }

    /**
     * @return HasMany
     */
    public function stocks(): HasMany
    {
        return $this->hasMany(Stock::class);
    }

    /**
     * @return BelongsToMany
     */
    public function stock(): BelongsToMany
    {
        return $this->belongsToMany(
            __CLASS__, 'product_stock_view'
        )
            ->withPivot([
                'stock', 'in_stock', 'sku', 'variation'
            ]);
    }

    /**
     * @return int
     */
    public function stockCount(): int
    {
        return (int)$this->stock?->first()?->pivot?->stock;
    }

    /**
     * @param int $count
     * @return mixed
     */
    public function minStock(
        int $count
    ): mixed
    {
        return min($this->stockCount(), $count);
    }

    /**
     * @return bool
     */
    public function inStock(): bool
    {
        return $this->stockCount() > 0;
    }


    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->sku = (string)Str::uuid();
        });
    }

}
