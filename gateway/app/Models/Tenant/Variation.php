<?php

namespace App\Models\Tenant;

use App\Foundation\FileManager\Traits\InteractWithMedia;
use App\Models\Traits\GenerateIdentifier;
use App\Models\Traits\HasPrice;
use App\Plugins\Moneys;
use Illuminate\Database\Eloquent\Casts\AsArrayObject;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Spatie\EloquentSortable\Sortable;
use Spatie\EloquentSortable\SortableTrait;
use Staudenmeir\LaravelAdjacencyList\Eloquent\HasRecursiveRelationships;


class Variation extends Model implements Sortable
{
    use SortableTrait,
        GenerateIdentifier, HasRecursiveRelationships, HasPrice, InteractWithMedia;

    /**
     * add the relation name for securing the key in db
     * @var string|null
     */
    protected string $relation;


    /**
     * @var string[]
     */
    protected $fillable = [
        'name', 'product_id', 'box_id', 'option_id', 'sku', 'sku_id', 'price', 'resale_price', 'parent_id', 'sort',
        'incremental', 'published', 'override', 'expire_after', 'expire_date',
        'default_selected', 'switch_price', 'appendage', 'child', 'single', 'input_type', 'upto', 'mime_type', 'properties'
    ];

    protected $casts = [
        'properties' => AsArrayObject::class,
    ];

    /**
     * sorting tables column
     * @var array
     */
    public $sortable = [
        'order_column_name' => 'sort',
        'sort_when_creating' => true,
    ];

    public function sku()
    {
        return $this->belongsTo(Sku::class, 'sku', 'sku');
    }

    /**
     * @return belongsTo
     */
    public function box(): belongsTo
    {
        return $this->belongsTo(Box::class, 'box_id', 'row_id')
            ->where('iso', app()->getLocale());
    }

    /**
     * @return HasOne
     */
    public function option(): belongsTo
    {
        return $this->belongsTo(Option::class, 'option_id', 'row_id')
            ->where('iso', app()->getLocale());
    }

    /**
     * @return BelongsTo
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id', 'row_id')
            ->where('iso', app()->getLocale());
    }

    /**
     * @param $value
     * @return mixed
     */
    public function getNameAttribute($value): mixed
    {
        if (!$value) {
            return $this->option->name;
        }
        return $value;
    }

    /**
     * @param int|null $value
     * @return Moneys
     */
    final public function getPriceAttribute(
        ?int $value
    ): Moneys
    {
//        if(!$value){
//            return (new \App\Plugins\Moneys())->setAmount(0);
//        }

        /// check override
        if ($this->override) {
            if (
                (!$value && !$this->incremental) ||
                ($value === 0 && !$this->incremental)) {
                if($this->product?->sku?->price instanceof Moneys) {
                    return $this->product?->sku?->price;
                }
                return (new Moneys())->setAmount($this->product?->sku?->price??0);
            }
        } elseif (!$this->override && !$this->option->incremental) {
            //optional($this->option->price)->amount() === 0 &&
//            if(!$this->option->incremental) {
            if($this->product?->sku?->price instanceof Moneys) {
                return $this->product?->sku?->price;
            }
            return (new Moneys())->setAmount($this->product?->sku?->price??0);
//            }
//            return $this->option->price;
        }
        return (new Moneys())->setAmount((int)$value);
    }

    /**
     * @return bool
     */
    final public function priceVaries(): bool
    {
//        dump($this->price->amount() , $this->product?->sku?->price?->amount());
        return $this->price->amount() !== $this->product?->sku?->price?->amount();
    }

    public function stocks()
    {
        return $this->hasMany(Stock::class, 'sku', 'sku');
    }

    /**
     * @return BelongsToMany
     */
    public function stock(): BelongsToMany
    {
        return $this->belongsToMany(
            __CLASS__, 'product_stock_view',
            'sku_id',
            'sku_id',
            'sku_id',
            'sku_id'
        )
            ->withPivot([
                'stock', 'in_stock', 'sku', 'variation'
            ]);
    }

    /**
     * @return mixed
     */
    public function stockCount(): mixed
    {
        return (int)optional(optional($this->stock->first())->pivot)->stock;
        //        return $this->descendantsAndSelf()->sum(fn ($variation) => $variation->stockes->sum('amount'));
//        return $this->ancestorsAndSelf()->ordered();
    }

    /**
     * @return mixed
     */
    public function inStock(): mixed
    {
        return (bool)$this->stock->first()->pivot->in_stock;
    }

    /**
     * @param $count
     * @return mixed
     */
    public function minStock(
        int $count
    ): mixed
    {
        return min($this->stockCount(), $count);
    }

    public function registerMediaCollections(): void
    {
        $this
            ->addMediaCollection('custom-variation')
            ->acceptsMimeTypes(['image/jpeg'])
            ->useDisk('assets');
    }

    /**
     * @return HasMany
     */
//    public function children(): HasMany
//    {
//        return $this->hasMany(__CLASS__, 'id', 'parent_id');
//    }

}
