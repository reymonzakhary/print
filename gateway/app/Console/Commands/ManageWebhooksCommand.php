<?php

namespace App\Console\Commands;

use App\Models\Domain;
use App\Models\PluginWebhookEvent;
use App\Enums\PluginStatus;
use Illuminate\Console\Command;

class ManageWebhooksCommand extends Command
{
    protected $signature = 'plugin:webhooks {action} {--limit=50} {--plugin=} {--hostname=} {--all-tenants}';

    protected $description = 'Manage plugin webhooks for multi-tenant system (retry, stats, clean)';

    public function handle(): void
    {
        $action = $this->argument('action');

        // Ensure we're using system database for webhook management
        \DB::setDefaultConnection('system');

        match ($action) {
            'retry' => $this->retryFailedWebhooks(),
            'stats' => $this->showStatistics(),
            'clean' => $this->cleanOldWebhooks(),
            'list' => $this->listWebhooks(),
            'process-pending' => $this->processPendingWebhooks(),
            default => $this->error("Unknown action: {$action}. Available: retry, stats, clean, list, process-pending")
        };
    }

    private function retryFailedWebhooks(): void
    {
        $limit = (int) $this->option('limit');
        $hostnameId = $this->option('hostname');

        if ($hostnameId) {
            $retried = $this->retryForSpecificTenant($hostnameId, $limit);
            $this->info("Retried {$retried} failed webhook(s) for hostname {$hostnameId}");
        } elseif ($this->option('all-tenants')) {
            $totalRetried = $this->retryForAllTenants($limit);
            $this->info("Retried {$totalRetried} failed webhook(s) across all tenants");
        } else {
            $this->error('Please specify --hostname=ID or --all-tenants');
        }
    }

    private function retryForSpecificTenant(int $hostnameId, int $limit): int
    {
        $hostname = Domain::find($hostnameId);
        if (!$hostname) {
            $this->error("Hostname {$hostnameId} not found");
            return 0;
        }

        $retried = 0;

        PluginWebhookEvent::where('hostname_id', $hostnameId)
            ->where('status', PluginStatus::FAILED)
            ->where('attempts', '<', 3)
            ->limit($limit)
            ->each(function (PluginWebhookEvent $event) use (&$retried) {
                if ($event->canRetry()) {
                    \App\Plugins\Jobs\PluginWebhookJob::dispatch($event, $event->hostname_id);
                    $retried++;
                }
            });

        return $retried;
    }

    private function retryForAllTenants(int $limit): int
    {
        $totalRetried = 0;

        $hostnames = $this->getPluginEnabledHostnames();

        foreach ($hostnames as $hostname) {
            $retried = $this->retryForSpecificTenant($hostname->id, $limit);
            $totalRetried += $retried;

            if ($retried > 0) {
                $this->line("Hostname {$hostname->id} ({$hostname->fqdn}): {$retried} retried");
            }
        }

        return $totalRetried;
    }

    private function showStatistics(): void
    {
        $hostnameId = $this->option('hostname');

        if ($hostnameId) {
            $this->showStatsForTenant($hostnameId);
        } elseif ($this->option('all-tenants')) {
            $this->showGlobalStats();
            $this->line('');
            $this->showStatsByTenant();
        } else {
            $this->showGlobalStats();
        }
    }

    private function showStatsForTenant(int $hostnameId): void
    {
        $hostname = Domain::find($hostnameId);
        if (!$hostname) {
            $this->error("Hostname {$hostnameId} not found");
            return;
        }

        $stats = [
            'pending' => PluginWebhookEvent::where('hostname_id', $hostnameId)->where('status', PluginStatus::PENDING)->count(),
            'processing' => PluginWebhookEvent::where('hostname_id', $hostnameId)->where('status', PluginStatus::PROCESSING)->count(),
            'completed' => PluginWebhookEvent::where('hostname_id', $hostnameId)->where('status', PluginStatus::COMPLETED)->count(),
            'failed' => PluginWebhookEvent::where('hostname_id', $hostnameId)->whereIn('status', [PluginStatus::FAILED, PluginStatus::FAILED_PERMANENTLY])->count(),
            'total' => PluginWebhookEvent::where('hostname_id', $hostnameId)->count(),
        ];

        $this->info("Statistics for hostname {$hostnameId} ({$hostname->fqdn}):");
        $this->table(
            ['Status', 'Count'],
            collect($stats)->map(fn($count, $status) => [ucfirst($status), $count])->toArray()
        );
    }

    private function showGlobalStats(): void
    {
        $stats = [
            'pending' => PluginWebhookEvent::where('status', PluginStatus::PENDING)->count(),
            'processing' => PluginWebhookEvent::where('status', PluginStatus::PROCESSING)->count(),
            'completed' => PluginWebhookEvent::where('status', PluginStatus::COMPLETED)->count(),
            'failed' => PluginWebhookEvent::whereIn('status', [PluginStatus::FAILED, PluginStatus::FAILED_PERMANENTLY])->count(),
            'total' => PluginWebhookEvent::count(),
        ];

        $this->info("Global webhook statistics:");
        $this->table(
            ['Status', 'Count'],
            collect($stats)->map(fn($count, $status) => [ucfirst($status), $count])->toArray()
        );
    }

    private function showStatsByTenant(): void
    {
        $this->info("Statistics by tenant:");

        // Get raw data and process in PHP instead of complex SQL
        $allEvents = PluginWebhookEvent::select('hostname_id', 'status')
            ->get()
            ->groupBy('hostname_id');

        if ($allEvents->isEmpty()) {
            $this->info("No webhook events found.");
            return;
        }

        $rows = [];
        foreach ($allEvents as $hostnameId => $events) {
            $hostname = Domain::find($hostnameId);

            $stats = [
                'total' => $events->count(),
                'pending' => $events->where('status', PluginStatus::PENDING)->count(),
                'processing' => $events->where('status', PluginStatus::PROCESSING)->count(),
                'completed' => $events->where('status', PluginStatus::COMPLETED)->count(),
                'failed' => $events->whereIn('status', [PluginStatus::FAILED, PluginStatus::FAILED_PERMANENTLY])->count(),
            ];

            if ($stats['total'] > 0) {
                $rows[] = [
                    $hostnameId,
                    $hostname ? $hostname->fqdn : 'Unknown',
                    $stats['total'],
                    $stats['pending'],
                    $stats['processing'],
                    $stats['completed'],
                    $stats['failed'],
                ];
            }
        }

        $this->table(
            ['Hostname ID', 'FQDN', 'Total', 'Pending', 'Processing', 'Completed', 'Failed'],
            $rows
        );
    }

    private function cleanOldWebhooks(): void
    {
        $days = 30;

        $deleted = PluginWebhookEvent::where('created_at', '<', now()->subDays($days))
            ->whereIn('status', [PluginStatus::COMPLETED, PluginStatus::FAILED_PERMANENTLY])
            ->delete();

        $this->info("Cleaned {$deleted} old webhook event(s)");
    }

    private function listWebhooks(): void
    {
        $query = PluginWebhookEvent::query()->latest();

        if ($plugin = $this->option('plugin')) {
            $query->whereJsonContains('plugin_config->plugin_name', $plugin);
        }

        if ($hostname = $this->option('hostname')) {
            $query->where('hostname_id', $hostname);
        }

        $webhooks = $query->limit(20)->get();

        $this->table(
            ['ID', 'Hostname', 'Plugin', 'Model', 'Event', 'Status', 'Attempts', 'Created'],
            $webhooks->map(function ($webhook) {
                $hostname = Domain::find($webhook->hostname_id);
                return [
                    $webhook->id,
                    $hostname ? $hostname->fqdn : 'Unknown',
                    $webhook->plugin_config['plugin_name'] ?? 'N/A',
                    class_basename($webhook->model_type),
                    $webhook->event_type,
                    $webhook->status->value ?? $webhook->status,
                    $webhook->attempts,
                    $webhook->created_at->format('Y-m-d H:i:s'),
                ];
            })->toArray()
        );
    }

    private function processPendingWebhooks(): void
    {
        $limit = (int) $this->option('limit');
        $processed = 0;

        PluginWebhookEvent::where('status', PluginStatus::PENDING)
            ->limit($limit)
            ->each(function (PluginWebhookEvent $event) use (&$processed) {
                \App\Plugins\Jobs\PluginWebhookJob::dispatch($event, $event->hostname_id);
                $processed++;
            });

        $this->info("Queued {$processed} pending webhook(s) for processing");
    }

    private function getPluginEnabledHostnames()
    {
        return Domain::whereHas('website', function($query) {
            $query->where('supplier', true)
                ->where('external', true)
                ->whereNotNull('configure');
        })->get();
    }
}
