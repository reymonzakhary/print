<?php

namespace App\Models\Tenants;

use App\Models\Tenants\Builders\QuotationBuilder;
use App\Models\Tenants\Trait\HasAddresses;
use App\Models\Traits\CanBeScoped;
use App\Models\Traits\GenerateIdentifier;
use App\Models\Traits\HasParentModel;
use App\Models\Traits\HasPrice;
use App\Models\Traits\InteractsWithMedia;
use App\Models\Traits\Tenant\BelongToTenantContext;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class Quotation extends Model
{
    use BelongToTenantContext,
        CanBeScoped,
        GenerateIdentifier,
        HasAddresses,
        HasFactory,
        HasParentModel,
        HasPrice,
        InteractsWithMedia,
        SoftDeletes,
        // UsesTenantConnection; // Removed

    /**
     * @var string
     */
    protected $table = 'orders';

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
        'connection',
        'message',
        'author_id',
        'editing'
    ];

    /**
     * @var array
     */
    protected $dates = [
        'expire_at',
        'locked_at'
    ];

    /**
     * Boots the model.
     *
     * @return void
     */
//    protected static function booted(): void
//    {
//        static::addGlobalScope('type', function (
//            \Illuminate\Database\Eloquent\Builder $builder
//        ) {
//            $builder->where('type', false);
//        });
//    }

    /**
     * @param mixed $value
     * @param null $field
     *
     * @return Model|void|null
     *
     * @throws ValidationException
     */
    public function resolveRouteBinding($value, $field = null)
    {
        Validator::make(
            [
                'quotation_id' => $value
            ],
            [
                'quotation_id' => 'integer|min:1'
            ]
        )->validate();

        return $this->where([['id', $value], ['type', false]])
            #->whereOwnerOrAllowed() Temp for now
            ->first() ?? abort(404, __('Not Found -- There is no quotation found'));
    }

    /**
     * @return BelongsTo
     */
    public function lockedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'locked_by', 'id', 'order_id')
            ->withTrashed();
    }

    /**
     * @return BelongsTo
     */
    public function orderedBy(): BelongsTo
    {
        return $this->belongsTo(Member::class, 'user_id', 'id', 'order_id')
            ->withTrashed();
    }

    /**
     * @return BelongsToMany
     */
    public function items()
    {
        return $this->belongsToMany(Item::class, 'order_items', 'order_id')->withPivot(
            'qty', 'delivery_pickup', 'shipping_cost', 'vat', 'sku_id'
        )->withTimestamps();
    }

    /**
     * @return HasMany
     */
    public function history()
    {
        return $this->hasMany(OrderHistory::class, 'order_id', 'id');
    }

    /**
     * @return BelongsToMany
     */
    public function services()
    {
        return $this->belongsToMany(Service::class, 'order_items', 'order_id')->withPivot(
            'vat', 'qty'
        )->withTimestamps();
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
     * @return BelongsTo
     */
    public function discount(): BelongsTo
    {
        return $this->belongsTo(Discount::class, 'discount_id', 'id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Builder|QuotationBuilder
     */
    public static function query(): \Illuminate\Database\Eloquent\Builder|QuotationBuilder
    {
        return parent::query();
    }

    /**
     * @param Builder $query
     * @return Builder|QuotationBuilder
     */
    public function newEloquentBuilder($query): Builder|QuotationBuilder
    {
        return new QuotationBuilder($query);
    }

    /**
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function mailQueues()
    {
        return $this->morphMany(MailQueue::class, 'mail_queues', 'model', 'model_id', 'id');
    }

}
