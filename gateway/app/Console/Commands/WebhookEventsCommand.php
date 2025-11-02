<?php

namespace App\Console\Commands;

use App\Enums\PluginStatus;
use App\Models\PluginWebhookEvent;
use Illuminate\Console\Command;

class WebhookEventsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'webhook:events {--limit=10 : Number of recent events to show}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Show recent webhook events';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $limit = (int) $this->option('limit');

        $events = PluginWebhookEvent::latest()
            ->limit($limit)
            ->get(['id', 'event_type', 'status', 'attempts', 'created_at', 'hostname_id']);

        if ($events->isEmpty()) {
            $this->info('No webhook events found.');
            return;
        }

        $this->info("Recent Webhook Events (Last {$limit})");
        $this->line('=====================================');

        $headers = ['ID', 'Event Type', 'Status', 'Attempts', 'Hostname', 'Created'];
        $rows = [];

        foreach ($events as $event) {
            $status = $event->status instanceof PluginStatus
                ? $event->status->value
                : (string) $event->status;

            $rows[] = [
                $event->id,
                $event->event_type,
                $status,
                $event->attempts ?? 0,
                $event->hostname_id,
                $event->created_at->format('Y-m-d H:i:s')
            ];
        }

        $this->table($headers, $rows);
    }
}
