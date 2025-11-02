<?php

namespace App\Plugins;

use App\Models\PluginWebhookEvent;
use App\Plugins\Jobs\PluginWebhookJob;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class WebhookQueueService
{
    /**
     * Available webhook queue connections
     */
    protected array $webhookQueues = [
        'webhook-high',
        'webhook-beanstalk',
        'webhook-redis',
        'webhook-db',
        'webhook-low',
        'webhook-failed',
    ];

    /**
     * Dispatch webhook job to the most appropriate queue
     */
    public function dispatch(
        PluginWebhookEvent $webhookEvent,
        int $hostnameId,
        array $options = []
    ): bool {
        try {
            $queueConnection = $this->determineQueue($webhookEvent, $options);
            $delay = $this->calculateDelay($webhookEvent, $options);

            PluginWebhookJob::dispatch($webhookEvent, $hostnameId)
                ->onConnection($queueConnection)
                ->delay($delay);

            // Track queue metrics
            $this->recordQueueMetrics($queueConnection, $webhookEvent);

            Log::info('Webhook job dispatched', [
                'webhook_event_id' => $webhookEvent->id,
                'queue_connection' => $queueConnection,
                'delay' => $delay,
                'hostname_id' => $hostnameId,
            ]);

            return true;

        } catch (\Exception $e) {
            Log::error('Failed to dispatch webhook job', [
                'webhook_event_id' => $webhookEvent->id,
                'hostname_id' => $hostnameId,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Determine the best queue connection based on webhook characteristics
     */
    protected function determineQueue(PluginWebhookEvent $webhookEvent, array $options = []): string
    {
        $routingRules = Config::get('queue.webhook_routing', []);
        $eventType = $webhookEvent->event_type;
        $priority = $options['priority'] ?? $this->calculatePriority($webhookEvent, $options);
        $delay = $options['delay'] ?? $this->calculateDelay($webhookEvent, $options);
        $attempts = $webhookEvent->attempts ?? 0;

        // Check for failed retry routing first
        if ($attempts > 3) {
            return $this->selectBestDriver($routingRules['failed_retry']['drivers'] ?? ['webhook-failed']);
        }

        // Check high priority conditions
        if ($this->matchesConditions($routingRules['high_priority']['conditions'] ?? [], $eventType, $priority, $delay)) {
            return $this->selectBestDriver($routingRules['high_priority']['drivers'] ?? ['webhook-high']);
        }

        // Check low priority conditions
        if ($this->matchesConditions($routingRules['low_priority']['conditions'] ?? [], $eventType, $priority, $delay)) {
            return $this->selectBestDriver($routingRules['low_priority']['drivers'] ?? ['webhook-low']);
        }

        // Default routing
        return $this->selectBestDriver($routingRules['default']['drivers'] ?? ['webhook-beanstalk', 'webhook-db']);
    }

    /**
     * Check if webhook matches routing conditions
     */
    protected function matchesConditions(array $conditions, string $eventType, int $priority, int $delay): bool
    {
        if (isset($conditions['event_types']) && !in_array($eventType, $conditions['event_types'])) {
            return false;
        }

        if (isset($conditions['priority'])) {
            if (!$this->evaluateCondition($priority, $conditions['priority'])) {
                return false;
            }
        }

        if (isset($conditions['delay'])) {
            if (!$this->evaluateCondition($delay, $conditions['delay'])) {
                return false;
            }
        }

        return true;
    }

    /**
     * Evaluate a condition array like ['>=', 8] or ['between', [3, 7]]
     */
    protected function evaluateCondition($value, array $condition): bool
    {
        [$operator, $compareTo] = $condition;

        return match($operator) {
            '>' => $value > $compareTo,
            '>=' => $value >= $compareTo,
            '<' => $value < $compareTo,
            '<=' => $value <= $compareTo,
            '=' => $value == $compareTo,
            '!=' => $value != $compareTo,
            'between' => $value >= $compareTo[0] && $value <= $compareTo[1],
            'in' => in_array($value, (array) $compareTo),
            default => false,
        };
    }

    /**
     * Select the best available driver from a list based on health and load
     */
    protected function selectBestDriver(array $drivers): string
    {
        $healthyDrivers = $this->filterHealthyDrivers($drivers);

        if (empty($healthyDrivers)) {
            return $drivers[0] ?? 'webhook-db';
        }

        return $this->selectLeastLoadedDriver($healthyDrivers);
    }

    /**
     * Filter out unhealthy queue drivers
     */
    protected function filterHealthyDrivers(array $drivers): array
    {
        return array_filter($drivers, function ($driver) {
            $cacheKey = "queue_health:{$driver}";
            $health = Cache::get($cacheKey, 'unknown');
            return $health !== 'unhealthy';
        });
    }

    /**
     * Select the driver with the lowest current load
     */
    protected function selectLeastLoadedDriver(array $drivers): string
    {
        $loads = [];

        foreach ($drivers as $driver) {
            $loads[$driver] = $this->getQueueLoad($driver);
        }

        return array_search(min($loads), $loads) ?: $drivers[0];
    }

    /**
     * Get current load for a queue driver
     */
    public function getQueueLoad(string $driver): int
    {
        $cacheKey = "queue_load:{$driver}";
        $cachedLoad = Cache::get($cacheKey);

        if ($cachedLoad !== null) {
            return $cachedLoad;
        }

        $load = $this->calculateDriverLoad($driver);
        Cache::put($cacheKey, $load, 30);

        return $load;
    }

    /**
     * Calculate actual load for a driver
     */
    protected function calculateDriverLoad(string $driver): int
    {
        $config = Config::get("queue.connections.{$driver}");

        if (!$config) {
            return 999;
        }

        try {
            return match($config['driver']) {
                'database' => $this->getDatabaseQueueLoad($config),
                'redis' => $this->getRedisQueueLoad($config),
                'beanstalkd' => $this->getBeanstalkdQueueLoad($config),
                default => 0,
            };
        } catch (\Exception $e) {
            Log::warning("Failed to get queue load for {$driver}", ['error' => $e->getMessage()]);
            return 999;
        }
    }

    /**
     * Get database queue load
     */
    protected function getDatabaseQueueLoad(array $config): int
    {
        $connection = $config['connection'] ?? 'system';
        $table = $config['table'] ?? 'jobs';
        $queue = $config['queue'] ?? 'default';

        return DB::connection($connection)->table($table)
            ->where('queue', $queue)
            ->whereNull('reserved_at')
            ->count();
    }

    /**
     * Get Redis queue load
     */
    protected function getRedisQueueLoad(array $config): int
    {
        try {
            $redis = app('redis')->connection($config['connection'] ?? 'default');
            $queueName = $config['queue'] ?? 'default';
            return $redis->llen('queues:' . $queueName);
        } catch (\Exception $e) {
            Log::warning("Failed to get Redis queue load", ['error' => $e->getMessage()]);
            return 999;
        }
    }

    /**
     * Get Beanstalkd queue load
     */
    protected function getBeanstalkdQueueLoad(array $config): int
    {
        try {
            return 0; // Beanstalkd is generally very fast, assume low load
        } catch (\Exception $e) {
            Log::warning("Failed to get Beanstalkd queue load", ['error' => $e->getMessage()]);
            return 999;
        }
    }

    /**
     * Calculate priority based on webhook characteristics
     */
    protected function calculatePriority(PluginWebhookEvent $webhookEvent, array $options = []): int
    {
        if (isset($options['priority'])) {
            return $options['priority'];
        }

        $priority = 5;

        $highPriorityEvents = ['payment_completed', 'order_cancelled', 'urgent_notification'];
        if (in_array($webhookEvent->event_type, $highPriorityEvents)) {
            $priority += 3;
        }

        if ($webhookEvent->attempts > 0) {
            $priority += $webhookEvent->attempts;
        }

        $lowPriorityEvents = ['data_sync', 'report_generation', 'cleanup'];
        if (in_array($webhookEvent->event_type, $lowPriorityEvents)) {
            $priority -= 2;
        }

        return max(1, min(10, $priority));
    }

    /**
     * Calculate delay based on webhook configuration
     */
    protected function calculateDelay(PluginWebhookEvent $webhookEvent, array $options = []): int
    {
        if (isset($options['delay'])) {
            return $options['delay'];
        }

        $config = $webhookEvent->plugin_config ?? [];
        return $config['delay'] ?? 0;
    }

    /**
     * Record queue metrics for monitoring
     */
    protected function recordQueueMetrics(string $connection, PluginWebhookEvent $webhookEvent): void
    {
        if (!Config::get('queue.monitoring.enabled', env('WEBHOOK_QUEUE_MONITORING', false))) {
            return;
        }

        $date = now()->format('Y-m-d');
        $cacheKey = "queue_metrics:{$connection}:{$date}";

        $metrics = Cache::get($cacheKey, [
            'dispatched' => 0,
            'event_types' => [],
        ]);

        $metrics['dispatched']++;
        $metrics['event_types'][$webhookEvent->event_type] =
            ($metrics['event_types'][$webhookEvent->event_type] ?? 0) + 1;

        Cache::put($cacheKey, $metrics, now()->addDays(1));
    }

    /**
     * Get queue health status
     */
    public function getQueueHealth(): array
    {
        $health = [];

        foreach ($this->webhookQueues as $connection) {
            try {
                $load = $this->getQueueLoad($connection);
                $health[$connection] = [
                    'status' => $load < 50 ? 'healthy' : ($load < 200 ? 'warning' : 'unhealthy'),
                    'load' => $load,
                    'last_checked' => now()->toISOString(),
                ];
            } catch (\Exception $e) {
                $health[$connection] = [
                    'status' => 'error',
                    'load' => 999,
                    'last_checked' => now()->toISOString(),
                    'error' => $e->getMessage(),
                ];
            }
        }

        return $health;
    }

    /**
     * Get queue metrics for monitoring dashboard
     */
    public function getQueueMetrics(int $days = 7): array
    {
        $metrics = [];

        for ($i = 0; $i < $days; $i++) {
            $date = now()->subDays($i)->format('Y-m-d');

            foreach ($this->webhookQueues as $connection) {
                $cacheKey = "queue_metrics:{$connection}:{$date}";
                $dayMetrics = Cache::get($cacheKey, ['dispatched' => 0, 'event_types' => []]);
                $metrics[$date][$connection] = $dayMetrics;
            }
        }

        return $metrics;
    }

    /**
     * Test all webhook queue connections
     */
    public function testConnections(): array
    {
        $results = [];

        foreach ($this->webhookQueues as $connection) {
            try {
                $load = $this->getQueueLoad($connection);
                $results[$connection] = [
                    'status' => 'success',
                    'load' => $load,
                    'message' => 'Connection successful'
                ];
            } catch (\Exception $e) {
                $results[$connection] = [
                    'status' => 'failed',
                    'load' => null,
                    'message' => $e->getMessage()
                ];
            }
        }

        return $results;
    }

    /**
     * Rebalance queue loads by redistributing jobs
     */
    public function rebalanceQueues(): array
    {
        $report = ['moved' => 0, 'errors' => []];

        try {
            $loads = [];
            foreach ($this->webhookQueues as $connection) {
                $loads[$connection] = $this->getQueueLoad($connection);
            }

            $maxLoad = max($loads);
            $minLoad = min($loads);

            if ($maxLoad - $minLoad > 100) {
                $overloaded = array_keys(array_filter($loads, fn($load) => $load > ($maxLoad * 0.8)));
                $underloaded = array_keys(array_filter($loads, fn($load) => $load < ($minLoad * 1.2)));

                $report['moved'] = $this->moveJobsBetweenQueues($overloaded, $underloaded);
            }

        } catch (\Exception $e) {
            $report['errors'][] = $e->getMessage();
        }

        return $report;
    }

    /**
     * Move jobs between queues (implementation depends on drivers)
     */
    protected function moveJobsBetweenQueues(array $from, array $to): int
    {
        $moved = 0;

        foreach ($from as $fromQueue) {
            $toQueue = $to[array_rand($to)] ?? null;
            if (!$toQueue) continue;

            $fromConfig = Config::get("queue.connections.{$fromQueue}");
            $toConfig = Config::get("queue.connections.{$toQueue}");

            if ($fromConfig['driver'] === 'database' && $toConfig['driver'] === 'database') {
                try {
                    $jobs = DB::connection($fromConfig['connection'])
                        ->table($fromConfig['table'])
                        ->where('queue', $fromConfig['queue'])
                        ->whereNull('reserved_at')
                        ->limit(10)
                        ->get();

                    foreach ($jobs as $job) {
                        DB::connection($toConfig['connection'])
                            ->table($toConfig['table'])
                            ->insert([
                                'queue' => $toConfig['queue'],
                                'payload' => $job->payload,
                                'attempts' => $job->attempts,
                                'reserved_at' => null,
                                'available_at' => $job->available_at,
                                'created_at' => $job->created_at,
                            ]);

                        DB::connection($fromConfig['connection'])
                            ->table($fromConfig['table'])
                            ->where('id', $job->id)
                            ->delete();

                        $moved++;
                    }
                } catch (\Exception $e) {
                    Log::warning("Failed to move jobs between queues", [
                        'from' => $fromQueue,
                        'to' => $toQueue,
                        'error' => $e->getMessage()
                    ]);
                }
            }
        }

        return $moved;
    }

    /**
     * Clear queue metrics cache
     */
    public function clearMetricsCache(): void
    {
        foreach ($this->webhookQueues as $connection) {
            Cache::forget("queue_load:{$connection}");
            Cache::forget("queue_health:{$connection}");

            for ($i = 0; $i < 30; $i++) {
                $date = now()->subDays($i)->format('Y-m-d');
                Cache::forget("queue_metrics:{$connection}:{$date}");
            }
        }
    }

    /**
     * Get queue name for a connection
     */
    protected function getQueueName(string $connection): string
    {
        $config = Config::get("queue.connections.{$connection}");
        return $config['queue'] ?? 'default';
    }

    /**
     * Get available webhook queues
     */
    public function getWebhookQueues(): array
    {
        return $this->webhookQueues;
    }
}
