<?php

namespace App\Models\Tenants;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QueueItem extends Model
{
    use HasFactory;

    /**
     * @var string[]
     */
    protected $fillable = [
        'model', 'payload', 'attempts', 'priority', 'decision', 'queueable', 'await', 'start_at', 'end_at',
        'step', 'busy'
    ];


    /**
     * @var string[]
     */
    protected $casts = [
        'payload' => 'object',
        'start_at' => 'datetime',
        'end_at' => 'datetime'
    ];

    /**
     * @return BelongsTo
     */
    public function queue(): BelongsTo
    {
        return $this->belongsTo(Queue::class);
    }
}
