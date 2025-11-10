<?php

namespace App\Models\Tenant;

use App\Models\Tenant\Builders\OrderBuilder;
use App\Models\Tenant\Trait\HasAddresses;
use App\Models\Traits\CanBeScoped;
use App\Models\Traits\GenerateIdentifier;
use App\Models\Traits\HasOrderNumber;
use App\Models\Traits\HasParentModel;
use App\Models\Traits\HasPrice;
use App\Models\Traits\InteractsWithMedia;
use App\Models\Traits\Tenant\BelongToTenantContext;
use App\Models\Traits\VirtualColumn;
use App\Plugins\Traits\PluginWebhookTrait;
use Carbon\Carbon;
use DateTimeInterface;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Validation\ValidationException;

/**
 * Class Order
 * @package App\Models\Tenants
 */
class Order extends Model
{
    use GenerateIdentifier,
        HasParentModel, CanBeScoped, SoftDeletes,
        BelongToTenantContext, HasPrice,
        HasOrderNumber, InteractsWithMedia, HasAddresses, VirtualColumn, PluginWebhookTrait;

    /**
     * @var array
     */
    protected $fillable = [
        'reference',
        'order_nr',
        'discount_id',
        'type',
        'st',
        'user_id',
        'delivery_multiple',
        'delivery_pickup',
        'shipping_cost',
        'price',
        'note',
        'created_from',
        'ctx_id',
        'expire_at',
        'locked_by',
        'locked_at',
        'locked',
        'created_by',
        'updated_by',
        'properties', // IMPORTANT: Include the data column itself
        'st_message',
        'connection',
        'internal',
        'team_id',
        'message',
        'author_id',
        'archived',
        'editing',
        // Add any virtual attributes you want to be mass-assignable
        'order_ref_id',
        'supplier'
    ];

    /**
     * @var array
     */
    protected $dates = [
        'expire_at',
        'locked_at'
    ];

    /**
     * @var string[]
     */
    protected $casts = [
        "expire_at" => 'datetime',
    ];

    /**
     * @return string
     */
    public static function getDataColumn(): string
    {
        return 'properties';
    }

    /**
     * @return string[]
     */
    public static function getCustomColumns(): array
    {
        return [
            'id', // IMPORTANT: Always include id
            'reference',
            'order_nr',
            'discount_id',
            'type',
            'st',
            'user_id',
            'delivery_multiple',
            'delivery_pickup',
            'shipping_cost',
            'price',
            'note',
            'created_from',
            'ctx_id',
            'expire_at',
            'locked_by',
            'locked_at',
            'locked',
            'created_by',
            'updated_by',
            'properties', // IMPORTANT: Include the data column itself
            'st_message',
            'connection',
            'internal',
            'team_id',
            'message',
            'author_id',
            'archived',
            'editing',
            'created_at', // IMPORTANT: Include timestamps
            'updated_at',
            'deleted_at'
        ];
    }

    protected static function booted(): void
    {
        static::updating(function ($order) {
            if ($order->getOriginal('archived')) {
                throw ValidationException::withMessages([
                    'order' => __('orders.archived')
                ]);
            }
        });
    }

    /**
     * Control when webhooks should fire
     */
    public function shouldTriggerWebhook(string $event): bool
    {
        return $event === 'created';
    }

    /**
     * Set custom webhook priorities based on event and order characteristics
     */
    public function getWebhookPriority(string $event): int
    {
       return 2;
    }

    /**
     * @param DateTimeInterface $date
     * @return string|null
     */
    protected function serializeDate(DateTimeInterface $date)
    {
        return Carbon::instance($date)->toISOString(true);
    }

    /**
     * @param mixed $value
     * @param null  $field
     * @return Model|void|null
     */
    public function resolveRouteBinding($value, $field = null)
    {
        return $this->where([['id', $value], ['type', true]])
            ->whereOwnerOrAllowed()
            ->first() ?? abort(404, __('Not Found -- There is no order found'));
    }

    /**
     * @return BelongsTo
     */
    public function lockedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'locked_by', 'id')
            ->withTrashed();
    }

    /**
     * @return BelongsTo
     */
    public function orderedBy(): BelongsTo
    {
        return $this->belongsTo(Member::class, 'user_id', 'id')
            ->withTrashed();
    }

    /**
     * @return BelongsToMany
     */
    public function items(): BelongsToMany
    {
        return $this->belongsToMany(Item::class, 'order_items', 'order_id', 'item_id')->orderBy('items.id')->withPivot(
            'qty', 'delivery_pickup', 'shipping_cost', 'vat'
        )->with('tags')->withTimestamps();
    }

    /**
     * @return HasMany
     */
    public function history(): HasMany
    {
        return $this->hasMany(OrderHistory::class);
    }

    /**
     * @return BelongsToMany
     */
    public function services(): BelongsToMany
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
        return $this->belongsTo(Discount::class, 'discount_id', 'id');
    }

    /**
     * @param $builder
     * @return mixed
     */
    public function scopeSystemOrders($builder): mixed
    {
        return $builder->where('created_from', 'system');
    }

    /**
     * @return MorphOne
     */
    public function vat(): MorphOne
    {
        return $this->morphOne(Vat::class, 'vatable');
    }

    /**
     * @return BelongsTo
     */
    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class, 'team_id');
    }

    /**
     * @return BelongsTo
     */
    public function author(): BelongsTo
    {
        return $this->belongsTo(Member::class, 'author_id', 'id', 'order_id')
            ->withTrashed();
    }

    /**
     * @return HasMany
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class, 'order_id', 'id');
    }

    /**
     * Use the custom OrderBuilder instead of VirtualColumnBuilder
     * The VirtualColumn trait's newEloquentBuilder will be ignored
     */
    public static function query(): \Illuminate\Database\Eloquent\Builder|OrderBuilder
    {
        return parent::query();
    }

    /**
     * @param Builder $query
     * @return Builder|OrderBuilder
     */
    public function newEloquentBuilder($query): Builder|OrderBuilder
    {
        return new OrderBuilder($query);
    }
}
