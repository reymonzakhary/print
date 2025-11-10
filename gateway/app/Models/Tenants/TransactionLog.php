<?php

declare(strict_types=1);

namespace App\Models\Tenants;

use Illuminate\Database\Eloquent\Casts\AsArrayObject;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TransactionLog extends Model
{


    protected $table = 'transactions_logs';

    protected $fillable = [
        'transaction_id',
        'st',
        'st_message',
        'type',
        'payload'
    ];

    protected $casts = [
        'payload' => AsArrayObject::class,
    ];

    /**
     * @return BelongsTo
     */
    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class, 'transaction_id', 'id');
    }
}
