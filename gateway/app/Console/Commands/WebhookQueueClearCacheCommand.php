<?php

namespace App\Console\Commands;

use App\Plugins\WebhookQueueService;
use Illuminate\Console\Command;

class WebhookQueueClearCacheCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'webhook:clear-cache';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clear webhook queue metrics cache';

    /**
     * Execute the console command.
     */
    public function handle(WebhookQueueService $queueService)
    {
        $queueService->clearMetricsCache();
        $this->info('Webhook queue metrics cache cleared successfully.');
    }
}
