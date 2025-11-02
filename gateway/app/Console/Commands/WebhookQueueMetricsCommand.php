<?php

namespace App\Console\Commands;

use App\Plugins\WebhookQueueService;
use Illuminate\Console\Command;

class WebhookQueueMetricsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'webhook:metrics {--days=7 : Number of days to show}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Show webhook queue metrics and statistics';

    /**
     * Execute the console command.
     */
    public function handle(WebhookQueueService $queueService)
    {
        $days = (int) $this->option('days');
        $metrics = $queueService->getQueueMetrics($days);

        $this->info("Webhook Queue Metrics (Last {$days} days)");
        $this->line('=====================================');

        if (empty($metrics)) {
            $this->comment('No metrics data available yet.');
            return;
        }

        foreach ($metrics as $date => $queueData) {
            $this->line("\n<comment>{$date}</comment>");

            foreach ($queueData as $queue => $data) {
                $dispatched = $data['dispatched'] ?? 0;
                $topEvent = '';

                if (!empty($data['event_types'])) {
                    $topEvents = array_keys($data['event_types']);
                    $topEvent = " (Top: " . implode(', ', array_slice($topEvents, 0, 2)) . ")";
                }

                $this->line("  {$queue}: {$dispatched} jobs{$topEvent}");
            }
        }
    }
}
