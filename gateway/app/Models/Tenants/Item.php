<?php

namespace App\Models\Tenants;

use App\Models\Tenant\Builders\ItemBuilder;
use App\Models\Tenant\Trait\HasAddresses;
use App\Models\Traits\GenerateIdentifier;
use App\Models\Traits\InteractsWithMedia;
use App\Plugins\Traits\PluginWebhookTrait;
use Illuminate\Database\Eloquent\Casts\AsArrayObject;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

/**
 * @property mixed $supplier_id
 * @property mixed st
 */
class Item extends Model
{
    use InteractsWithMedia, GenerateIdentifier, HasAddresses;

    protected $fillable = [
        'product', 'reference', 'discount_id', 'st', 'supplier_id', 'supplier_name', 'note',
        'delivery_separated', 'st_message', 'sku', 'sku_id', 'vat', 'connection', 'internal'
    ];

    /**
     * @var string[]
     */
    protected $appends = [];

    /**
     * @var string[]
     */
    protected $casts = [
        'product' => AsArrayObject::class
    ];
    protected static function booted(): void
    {
        static::updating(function ($item) {
            if (!is_null($item->pivot) && $item->pivot->pivotParent->getOriginal('archived')) {
                throw ValidationException::withMessages([
                    'order' => __('orders.archived')
                ]);
            }
        });
    }

    /**
     * @param Builder $query
     *
     * @return ItemBuilder|Builder
     */
    public function newEloquentBuilder($query): ItemBuilder|Builder
    {
        return new ItemBuilder($query);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Builder|ItemBuilder
     */
    public static function query(): \Illuminate\Database\Eloquent\Builder|ItemBuilder
    {
        return parent::query();
    }

    public function order()
    {
        return $this->belongsToMany(Order::class, 'order_items')->withPivot(
            'qty', 'delivery_pickup', 'shipping_cost'
        )->withTimestamps();
    }

    /**
     * @return BelongsToMany
     */
    public function quotation()
    {
        return $this->belongsToMany(Quotation::class, 'order_items', 'item_id', 'order_id')->withPivot(
            'qty', 'delivery_pickup', 'shipping_cost'
        )->withTimestamps();
    }

    public function children()
    {
        return $this->hasMany(OrderItem::class)->whereNull('order_id');
    }

    /**
     * @return BelongsToMany
     */
    public function services()
    {
        return $this->belongsToMany(Service::class, 'order_items')->withPivot(
            'vat', 'qty'
        )->withTimestamps();
    }

    /**
     * @return BelongsTo
     */
    public function discount(): BelongsTo
    {
        return $this->belongsTo(Discount::class);
    }

    /**
     * @return string|null
     */
    public function getSupplierNameAttribute($value): ?string
    {
        return $value ?? optional(optional($this->product)['prices'])['supplier_name'];
    }

    /**
     * @param $value
     * @return int
     */
    public function getDeliveryDaysAttribute($value): int
    {
        foreach ($this->product as $k => $v) {
            if ($k === "prices") {
                return optional($v)['dlv']['days'] ?? 0;
            }
        }
        return 0;
    }

    public function vat()
    {
        return $this->morphOne(Vat::class, 'vatable');
    }

    public function tags()
    {
        return $this->morphToMany(Tag::class, 'taggable');
    }

    public function sk()
    {
        return $this->belongsTo(Sku::class, 'sku_id', 'id');
    }
}
