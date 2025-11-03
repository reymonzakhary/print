<?php

namespace App\Plugins;

use App\Models\Domain;
use App\Models\PluginWebhookEvent;
use App\Models\Website;
use App\Plugins\Concrete\PluginManagerInterface;
use App\Plugins\Config\DefaultConfigRepository;
use App\Plugins\Config\PluginConfigRepository;
use App\Enums\PluginStatus;
use App\Plugins\Jobs\PluginWebhookJob;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use JetBrains\PhpStorm\NoReturn;

/**
 * @author Reymon Zakhary
 * Represents a facade for the Plugins class.
 * @method static static bus(Request $request, self $service)
 * @method static static getSyncPipelineConfig()
 * @method static static getCategories()
 * @method static static auth(array $array)
 */
class PluginService
{
    /**
     * Constant for the path to the plugins source directory within the Laravel application.
     */
    private const string PLUGIN_PATH = "\\App\\Plugins\\Src\\";

    private const string PLUGIN_WEBHOOKS = "\\App\\Plugins\\Webhooks\\";

    /**
     * @var Website $website
     */
    protected Website $website;

    /**
     * @var DefaultConfigRepository $instance
     */
    protected DefaultConfigRepository $instance;

    /**
     * @var PluginManagerInterface $plugin
     */
    private PluginManagerInterface $plugin;

    /**
     * Load the plugin configuration for the given Hostname instance.
     *
     * @param \Hyn\Tenancy\Models\Hostname|Hostname|int|null $hostname The hostname for which to load the plugin configuration
     * @return self
     */
    #[NoReturn] public function load(
        \Hyn\Tenancy\Models\Hostname|Hostname|int|null $hostname = null,
    ): self
    {
        $this->instance = app(PluginConfigRepository::class)->update($hostname);
        $plugins = App::make('plugin.managers');
        $this->plugin = $plugins[self::PLUGIN_PATH.Str::ucfirst($this->instance->getRoutePrefix())."PluginManager"] ?? null;
        return $this;
    }

    /**
     * Get webhook configurations for a model/event combination from database
     */
    public function getWebhookConfigurations(string $modelType, string $eventType): array
    {
        if (!$this->instance || !$this->instance->supportsWebhooks()) {
            return [];
        }

        // Get webhook configurations from the database configure field
        $webhookConfigurations = $this->instance->getWebhookConfiguration();

        return collect($webhookConfigurations)
            ->filter(function ($config) use ($modelType, $eventType) {
                return in_array($modelType, $config['models'] ?? []) &&
                    in_array($eventType, $config['events'] ?? []);
            })
            ->map(function ($config) {
                // Add plugin information to each configuration
                return array_merge($config, [
                    'plugin_name' => $this->instance->getPluginName(),
                    'plugin_port' => $this->instance->getPluginPort(),
                    'plugin_route_prefix' => $this->instance->getRoutePrefix(),
                    'hostname_id' => $this->instance->hostname->id,
                ]);
            })
            ->values()
            ->toArray();
    }

    /**
     * Get plugin manager for a specific plugin name
     */
    public function getPluginManagerByName(string $pluginName): ?PluginManagerInterface
    {
        $plugins = App::make('plugin.managers');

        foreach ($plugins as $className => $manager) {
            try {
                if ($manager instanceof PluginManagerInterface) {
                    $managerPluginName = $this->extractPluginNameFromClassName($className);
                    if ($managerPluginName === $pluginName) {
                        return $manager;
                    }
                }
            } catch (Exception $e) {
                Log::debug('Error checking plugin manager', [
                    'manager' => $className,
                    'error' => $e->getMessage()
                ]);
            }
        }

        return null;
    }

    /**
     * Extract plugin name from class name
     */
    private function extractPluginNameFromClassName(string $className): ?string
    {
        // Extract from class name like "\\App\\Plugins\\Src\\DwdPluginManager"
        $baseName = class_basename($className);
        $pluginName = Str::before($baseName, 'PluginManager');

        // Convert to lowercase with dots (e.g., "Dwd" -> "dwd", "Printcom" -> "printcom")
        return strtolower($pluginName);
    }

    /**
     * Trigger webhook for the current loaded plugin
     */
    public function triggerWebhook(
        string $endpoint,
        array $payload,
        string $eventType = 'manual_trigger',
        bool $async = true,
        array $options = []
    ): bool {

        if (!$this->instance || !$this->plugin) {
            Log::warning('Plugin not loaded when trying to trigger webhook');
            return false;
        }

        try {
            $enrichedPayload = array_merge($payload, [
                'model_type' => $options['model_type'] ?? 'manual',
                'event_type' => $eventType,
                'tenant_id' => $this->instance->hostname->website->uuid,
                'tenant_name' => $this->instance->hostname->fqdn,
            ]);

            $webhookEvent = PluginWebhookEvent::create([
                'hostname_id' => $this->instance->hostname->id,
                'model_type' => $options['model_type'] ?? 'manual',
                'model_id' => $options['model_id'] ?? null,
                'event_type' => $eventType,
                'payload' => $enrichedPayload,
                'plugin_config' => [
                    'plugin_name' => $this->instance->getPluginName(),
                    'webhook_endpoint' => $endpoint,
                    'queue_mode' => $async ? 'background' : 'foreground',
                    'tenant_id' => $this->instance->hostname->website->uuid,
                    'hostname_id' => $this->instance->hostname->id,
                    'delay' => $options['delay'] ?? 0,
                ],
                'status' => PluginStatus::PENDING,
                'attempts' => 0,
            ]);

            // Use WebhookQueueService for smart routing
            $queueService = app(WebhookQueueService::class);

            $queueOptions = [
                'priority' => $options['priority'] ?? 5,
                'delay' => $options['delay'] ?? 0,
            ];

            return $queueService->dispatch($webhookEvent, $this->instance->hostname->id, $queueOptions);

        } catch (Exception $e) {
            Log::error('Failed to trigger webhook', [
                'plugin' => $this->instance->getPluginName(),
                'endpoint' => $endpoint,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Process webhook through the current plugin
     */
    public function processWebhook(PluginWebhookEvent $webhookEvent): array
    {
        if (!$this->plugin) {
            throw new Exception('Plugin not loaded');
        }

        $config = $webhookEvent->plugin_config;
        $endpoint = $config['webhook_endpoint'] ?? 'webhook';

        // Register the plugin with current configuration
        $this->plugin->register(
            uri: $this->instance->getRoutePrefix(),
            port: $this->instance->getPluginPort(),
            routes: $this->instance->getPluginRoutes(),
            tenant_id: $config['hostname_id'],
            tenant_name: $this->instance->hostname->fqdn,
            hostname: $this->instance->hostname,
            configRepository: $this->instance
        );

        // Call the webhook method if it exists on the plugin
        if (method_exists($this->plugin, 'webhook')) {
            return $this->plugin->webhook($webhookEvent->payload, $endpoint);
        }

        // Fallback to makeRequest if webhook method doesn't exist
        if (method_exists($this->plugin, 'makeRequest')) {
            return $this->plugin->makeRequest(
                method: 'POST',
                requestUrl: $endpoint,
                formParams: [
                    'event_type' => $webhookEvent->event_type,
                    'model_type' => $webhookEvent->model_type,
                    'model_id' => $webhookEvent->model_id,
                    'data' => $webhookEvent->payload,
                    'timestamp' => $webhookEvent->created_at->toISOString(),
                    'webhook_id' => $webhookEvent->id,
                ],
                forceJson: true
            );
        }

        throw new Exception('Plugin does not support webhook processing');
    }

    /**
     * Retry failed webhook events for current tenant or globally
     */
    public function retryFailedWebhooks(int $limit = 50): int
    {
        $retried = 0;

        $query = PluginWebhookEvent::failed()->where('attempts', '<', 3);

        // If instance is loaded, filter by current hostname
        if ($this->instance && $this->instance->hostname) {
            $query->where('hostname_id', $this->instance->hostname->id);
        }

        $query->limit($limit)->each(function (PluginWebhookEvent $event) use (&$retried) {
            if ($event->canRetry()) {
                PluginWebhookJob::dispatch($event, $event->hostname_id);
                $retried++;
            }
        });

        return $retried;
    }

    /**
     * Get webhook statistics for current plugin/tenant
     */
    public function getWebhookStatistics(): array
    {
        $query = PluginWebhookEvent::query();

        // Filter by current hostname if instance is loaded
        if ($this->instance && $this->instance->hostname) {
            $query->where('hostname_id', $this->instance->hostname->id);

            // Optionally filter by plugin name
            $pluginName = $this->instance->getPluginName();
            if ($pluginName) {
                $query->whereJsonContains('plugin_config->plugin_name', $pluginName);
            }
        }

        return [
            'pending' => (clone $query)->where('status', PluginStatus::PENDING)->count(),
            'processing' => (clone $query)->where('status', PluginStatus::PROCESSING)->count(),
            'completed' => (clone $query)->where('status', PluginStatus::COMPLETED)->count(),
            'failed' => (clone $query)->whereIn('status', [PluginStatus::FAILED, PluginStatus::FAILED_PERMANENTLY])->count(),
            'total' => $query->count(),
        ];
    }

    /**
     * Get webhook statistics for specific hostname
     */
    public static function getWebhookStatisticsForHostname(int $hostnameId): array
    {
        $query = PluginWebhookEvent::where('hostname_id', $hostnameId);

        return [
            'pending' => (clone $query)->where('status', PluginStatus::PENDING)->count(),
            'processing' => (clone $query)->where('status', PluginStatus::PROCESSING)->count(),
            'completed' => (clone $query)->where('status', PluginStatus::COMPLETED)->count(),
            'failed' => (clone $query)->whereIn('status', [PluginStatus::FAILED, PluginStatus::FAILED_PERMANENTLY])->count(),
            'total' => $query->count(),
        ];
    }

    /**
     * Get all webhook events for current tenant
     */
    public function getWebhookEvents(int $limit = 50): \Illuminate\Database\Eloquent\Collection
    {
        $query = PluginWebhookEvent::query()->latest();

        if ($this->instance && $this->instance->hostname) {
            $query->where('hostname_id', $this->instance->hostname->id);
        }

        return $query->limit($limit)->get();
    }

    /**
     * Check if current tenant supports webhooks
     */
    public function supportsWebhooks(): bool
    {
        return $this->instance && $this->instance->supportsWebhooks();
    }

    /**
     * Dynamically handle calls to the class.
     *
     * @param string $method The method being called
     * @param array $parameters The parameters being passed to the method
     * @return mixed|null
     */
    public function __call(
        string $method,
        array $parameters
    )
    {
        if(method_exists($this->instance, $method)) {
            return $this->instance->{$method}(...$parameters);
        }

        if(method_exists($this->plugin, $method)) {
            $this->plugin->register(
                uri: $this->instance->getRoutePrefix(),
                port: $this->instance->getPluginPort(),
                routes: $this->instance->getPluginRoutes(),
                tenant_id: $this->instance->hostname->website->uuid,
                tenant_name: $this->instance->hostname->fqdn,
                hostname: $this->instance->hostname,
                configRepository: $this->instance
            );
            return call_user_func_array([$this->plugin, $method], array_merge($parameters,[$this->instance->getRoutePrefix()]));
        }

        return null;
    }

    private function getWebhookConnection(array $webhookConfig): string
    {
        // Use Redis for immediate processing
        if (($webhookConfig['delay'] ?? 0) === 0 && ($webhookConfig['priority'] ?? 'normal') === 'high') {
            return 'webhook-redis';
        }

        // Use Beanstalk for medium priority
        if (($webhookConfig['priority'] ?? 'normal') === 'medium') {
            return 'webhook-beanstalk';
        }

        // Default to database for standard webhooks
        return 'webhook-db';
    }

    private function getDefaultQueue(string $connection): string
    {
        return match($connection) {
            'webhook-redis' => 'plugin-webhooks-fast',
            'webhook-beanstalk' => 'plugin-webhooks-tube',
            'webhook-db' => 'plugin-webhooks',
            default => 'default'
        };
    }

}
