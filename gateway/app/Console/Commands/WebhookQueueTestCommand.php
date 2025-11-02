<?php

namespace App\Console\Commands;

use App\Plugins\WebhookQueueService;
use Illuminate\Console\Command;

class WebhookQueueTestCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'webhook:test';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test webhook queue connections and routing';

    /**
     * Execute the console command.
     */
    public function handle(WebhookQueueService $queueService)
    {
        $this->info('Testing webhook queue connections...');

        $results = $queueService->testConnections();

        foreach ($results as $queue => $result) {
            if ($result['status'] === 'success') {
                $this->line("<info>✓</info> {$queue}: {$result['message']} (Load: {$result['load']})");
            } else {
                $this->line("<error>✗</error> {$queue}: {$result['message']}");
            }
        }
    }
}
