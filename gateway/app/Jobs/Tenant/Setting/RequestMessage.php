<?php

declare(strict_types=1);

namespace App\Jobs\Tenant\Setting;

use App\Enums\MessageTo;
use App\Events\Messages\CrossTenantMessage;
use App\Models\Domain;
use App\Models\Message;
use Hyn\Tenancy\Environment;
use Illuminate\Bus\Queueable;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Http\Request;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Psr\Log\LoggerInterface;

class RequestMessage
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public Request $request
    ) {
    }

    /**
     * Execute the job.
     */
    public function handle(
        LoggerInterface   $logger,
    ): void
    {
        $message = Message::query()->create($this->request->all());

        /* @var Message $message */

        if ($message->getAttribute('to') === MessageTo::SUPPLIER) {
            $this->handleBroadcastNotification($message, $logger);
        }
    }

    /**
     * @param Message $message
     * @param LoggerInterface $logger
     *
     * @return void
     */
    private function handleBroadcastNotification(
        Message           $message,
        LoggerInterface   $logger,
    ): void
    {
        if (!$targetTenant = Domain::query()
            ->with('website')
            ->where('id', $message->getAttribute('recipient_hostname'))->first()
        ) {
            $logger->warning('Recipient of the message does not exist', [
                'message' => $message,
            ]);

            return;
        }

        app(Environment::class)->tenant($targetTenant->website);
        switchSupplierWebsocket($targetTenant->website->getAttribute('uuid'));
        event(new CrossTenantMessage($message));
        app(Environment::class)->tenant($this->request->tenant);
        switchSupplierWebsocket($this->request->tenant->getAttribute('uuid'));
    }
}
