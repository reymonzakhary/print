<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MailQueueLogs extends Model
{
    use HasFactory;

    /**
     * @var array
     */
    protected $fillable = [
        'message', 'st', 'trace'
    ];

    /**
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function mailQueue(): BelongsTo
    {
        return $this->belongsTo(MailQueue::class, 'mail_queue_id');
    }

}
