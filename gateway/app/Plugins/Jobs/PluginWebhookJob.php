<?php

namespace App\Plugins\Jobs;

use App\Models\Domain;
use App\Models\PluginWebhookEvent;
use App\Plugins\PluginService;
use Exception;
use Hyn\Tenancy\Facades\TenancyFacade;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PluginWebhookJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $maxExceptions = 3;
    public int $timeout = 300; // 5 minutes

    /**
     * @param PluginWebhookEvent $webhookEvent
     * @param int $hostnameId
     */
    public function __construct(
        public PluginWebhookEvent $webhookEvent,
        public int $hostnameId
    ) {
    }

    /**
     * Execute the job
     */
    public function handle(): void
    {
        // Ensure we're working with system database for webhook event updates
        $originalConnection = DB::getDefaultConnection();
        DB::setDefaultConnection('system');

        try {
            $this->webhookEvent->increment('attempts');
            $this->webhookEvent->update(['status' => 'processing']);

            // Get hostname and switch to tenant context
            $hostname = Domain::find($this->hostnameId);
            if (!$hostname || !$hostname->website) {
                throw new Exception("Hostname {$this->hostnameId} not found or has no website");
            }

            // Switch to tenant context
            TenancyFacade::website($hostname->website);

            // Load plugin service with tenant context
            $pluginService = app(PluginService::class);
            $pluginService->load($this->hostnameId);

            // Process the webhook
            $response = $pluginService->processWebhook($this->webhookEvent);

            // Update status (back in system database)
            DB::setDefaultConnection('system');
            $this->webhookEvent->update([
                'status' => 'completed',
                'response' => $response,
                'completed_at' => now(),
            ]);

            Log::info('Plugin webhook processed successfully', [
                'webhook_event_id' => $this->webhookEvent->id,
                'hostname_id' => $this->hostnameId,
                'plugin' => $this->webhookEvent->plugin_config['plugin_name'] ?? 'unknown',
                'event' => $this->webhookEvent->event_type,
            ]);

        } catch (Exception $e) {
            // Ensure we're back to system database for error handling
            DB::setDefaultConnection('system');
            $this->handleFailure($e);
        } finally {
            // Always restore original connection
            DB::setDefaultConnection($originalConnection);

            // Clear tenant context
            TenancyFacade::website(null);
        }
    }

    /**
     * Handle failure of plugin webhook event.
     *
     * @param Exception $e
     * @throws Exception
     */
    protected function handleFailure(Exception $e): void
    {
        Log::error('Plugin webhook failed', [
            'webhook_event_id' => $this->webhookEvent->id,
            'hostname_id' => $this->hostnameId,
            'error' => $e->getMessage(),
            'attempts' => $this->webhookEvent->attempts,
        ]);

        $this->webhookEvent->update([
            'status' => 'failed',
            'error_message' => $e->getMessage(),
            'failed_at' => now(),
        ]);

        // Re-throw to trigger Laravel's retry mechanism
        throw $e;
    }

    /**
     * Handle job failure after all retries
     */
    public function failed(Exception $exception): void
    {
        // Ensure we're using system database
        DB::setDefaultConnection('system');

        $this->webhookEvent->update([
            'status' => 'failed_permanently',
            'error_message' => $exception->getMessage(),
        ]);
    }
}
