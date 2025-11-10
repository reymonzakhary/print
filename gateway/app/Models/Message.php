<?php

namespace App\Models;

use App\Enums\MessageTo;
use App\Enums\MessageType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Staudenmeir\LaravelAdjacencyList\Eloquent\HasRecursiveRelationships;

class Message extends Model
{
    use HasFactory, HasRecursiveRelationships;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'title', 'subject', 'body', 'parent_id','sender_hostname', 'recipient_hostname',
        'type', 'to', 'from',
        'sender_email', 'sender_name', 'recipient_email', 'contract_id',
        'sender_user_id', 'recipient_user_id','read','read_at'
    ];

    /**
     * @var string[]
     */
    protected $casts = [
        'to' => MessageTo::class,
        'type' => MessageType::class,
    ];

    /**
     * @return BelongsTo]
     */
    public function contract(): BelongsTo
    {
        return $this->belongsTo(Contract::class);
    }


    public function markAsRead(): void
    {
        !$this->read?:
            $this->forceFill([
                'read' => true,
                'read_at' => now()
            ]);
    }
}
