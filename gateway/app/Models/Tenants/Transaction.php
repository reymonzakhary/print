<?php

declare(strict_types=1);

namespace App\Models\Tenants;

use App\Casts\Transaction\CustomFieldCast;
use App\Models\Traits\CanBeScoped;
use App\Models\Traits\HasInvoiceNumber;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Staudenmeir\LaravelAdjacencyList\Eloquent\HasRecursiveRelationships;

class Transaction extends Model
{
    use SoftDeletes, HasInvoiceNumber, HasRecursiveRelationships, CanBeScoped;

    protected $fillable = [
        'order_id',
        'invoice_nr',
        'payment_method',
        'st',
        'fee',
        'vat',
        'discount_id',
        'price',
        'custom_field',
        'company_id',
        'team_id',
        'user_id',
        'contract_id',
        'type',
        'parent_id',
        'due_date'
    ];

    protected $casts = [
        'custom_field' => CustomFieldCast::class,
    ];

    /**
     * @return BelongsTo
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'order_id', 'id');
    }

    /**
     * @return BelongsTo
     */
    public function discount(): BelongsTo
    {
        return $this->belongsTo(Discount::class, 'discount_id', 'id');
    }

    /**
     * @return BelongsTo
     */
    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class, 'team_id', 'id');
    }

    /**
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    /**
     * @return HasMany
     */
    public function logs(): HasMany
    {
        return $this->hasMany(TransactionLog::class, 'transaction_id', 'id');
    }

    /**
     *
     * @return MorphMany
     */
    public function mailQueues(): MorphMany
    {
        return $this->morphMany(MailQueue::class, 'mail_queues', 'model', 'model_id', 'id');
    }
}
