<?php

namespace App\Models\Tenants;

use Hyn\Tenancy\Traits\UsesTenantConnection;
use Illuminate\Database\Eloquent\Casts\AsArrayObject;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MailQueue extends Model
{
    use UsesTenantConnection;

    /**
     *
     * @var array
     */
    protected $fillable = [
        'model_id', 'model', 'st', 'message',
        'sent_at', 'from', 'to', 'subject', 'bcc', 'cc',
    ];

    /**
     *
     * @var array
     */
    protected $casts = [
        'message' => AsArrayObject::class
    ];

    /**
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function logs(): HasMany
    {
        return $this->hasMany(MailQueueLogs::class, 'mail_queue_id');
    }

    /**
     * Formatting the message json object
     * @param mixed $greeting
     * @param mixed $message
     * @param mixed $regards
     * @return array
     */
    public static function formatMessageObject(
        ?string $greeting = '',
        ?string $message = '',
        ?string $regards = ''
    ): array
    {
        return [
            'greeting' => $greeting??'',
            'message' => $message??'',
            'regards' => $regards??'',
        ];
    }

    /**
     * Creating logs for the mail queue
     * @param mixed $message
     * @param mixed $st
     * @param mixed $trace
     * @return Model
     */
    public function log(?string $message = '', ?int $st = null, ?string $trace = '')
    {
        return $this->logs()->create([
            'message' => $message,
            'st' => $st,
            'trace' => $trace
        ]);
    }
}
