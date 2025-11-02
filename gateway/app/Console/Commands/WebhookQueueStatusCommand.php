<?php

namespace App\Console\Commands;

use App\Plugins\WebhookQueueService;
use Illuminate\Console\Command;

class WebhookQueueStatusCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'webhook:status {--json : Output as JSON}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Show status of all webhook queues';

    /**
     * Execute the console command.
     */
    public function handle(WebhookQueueService $queueService)
    {
        $health = $queueService->getQueueHealth();

        if ($this->option('json')) {
            $this->line(json_encode($health, JSON_PRETTY_PRINT));
            return;
        }

        $this->info('Webhook Queue Status');
        $this->line('====================');

        foreach ($health as $queue => $data) {
            $statusColor = match($data['status']) {
                'healthy' => 'info',
                'warning' => 'comment',
                'unhealthy' => 'error',
                default => 'comment',
            };

            $statusIcon = match($data['status']) {
                'healthy' => '✓',
                'warning' => '⚠',
                'unhealthy' => '✗',
                default => '?',
            };

            $this->line(sprintf(
                '%-20s <%s>%s %s</> (Load: %d)',
                $queue,
                $statusColor,
                $statusIcon,
                strtoupper($data['status']),
                $data['load']
            ));
        }
    }
}
