<?php

namespace App\Console\Commands;

use App\Plugins\WebhookQueueService;
use Illuminate\Console\Command;

class WebhookQueueRebalanceCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'webhook:rebalance {--dry-run : Show what would be rebalanced}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Rebalance webhook queues to optimize load distribution';

    /**
     * Execute the console command.
     */
    public function handle(WebhookQueueService $queueService)
    {
        if ($this->option('dry-run')) {
            $this->info('DRY RUN - No changes will be made');
        }

        $this->info('Starting queue rebalancing...');

        $report = $queueService->rebalanceQueues();

        $this->info("Jobs moved: {$report['moved']}");

        if (!empty($report['errors'])) {
            $this->error('Errors encountered:');
            foreach ($report['errors'] as $error) {
                $this->error("  - {$error}");
            }
        }

        $this->info('Queue rebalancing completed');
    }
}
