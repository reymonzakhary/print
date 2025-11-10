<?php

namespace App\Plugins\Contracts;

use App\Models\Domain;
use App\Plugins\Concrete\PluginFactoryInterface;
use App\Plugins\Concrete\PluginManagerInterface;
use App\Plugins\Config\DefaultConfigRepository;
use App\Utilities\Traits\ConsumesExternalServices;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

abstract class PluginManager implements PluginManagerInterface
{
    use ConsumesExternalServices;

    /**
     *
     * @var string|null $base_uri The base URI of the Laravel application
     */
    protected ?string $base_uri;

    /**
     * @var int|null $port This variable represents the port number
     */
    protected ?int $port;

    /**
     * @var array $routes
     */
    protected array $routes = [];

    /**
     * @param int $tenant_id
     */
    protected string $tenant_id;

    /**
     * @@param string $tenant_name
     */
    protected string $tenant_name;

    /**
     * @param Domain $hostname
     */
    protected Domain $hostname;

    /**
     * @param DefaultConfigRepository $configRepository
     */
    protected DefaultConfigRepository $configRepository;

    /**
     * @param string $factory
     */
    private PluginFactoryInterface $factory;

    /**
     * @param mixed $pipeline
     */
    protected PluginStack $pipeline;

    /**
     * Register a new URI, port, and routes for the application.
     *
     * @param string $uri
     * @param int $port The port to associate with the URI.
     * @param array $routes The routes to associate with the URI.
     * @param string $tenant_id
     * @param string $tenant_name
     * @param Domain $hostname
     * @param DefaultConfigRepository $configRepository
     * @return void
     */
    public function register(
        string $uri,
        int $port,
        array $routes,
        string $tenant_id,
        string $tenant_name,
        Domain $hostname,
        DefaultConfigRepository $configRepository
    ): void
    {
        $this->base_uri = "{$uri}:{$port}";
        $this->tenant_id = $tenant_id;
        $this->tenant_name = $tenant_name;
        $this->routes = $routes;
        $this->hostname = $hostname;
        $this->configRepository = $configRepository;
        $this->factory = app(PluginFactoryInterface::class);
        $this->pipeline = app(PluginStack::class);
    }

    /**
     * Process the bus logic by iterating through the runner array items.
     *
     * @param Request $request The request object containing data.
     * @param array $runner The array of items for bus processing.
     * @return void
     */
    public function bus(
        Request $request,
        array $runner = [],
    ): void
    {
        $counter = count($runner);
        $runner = json_decode(json_encode($runner));
        $item = reset($runner);
        $runner = collect($runner)->map(function ($item, $index) use ($runner) {
            $item->last = ($index === collect($runner)->count() - 1);
            return $item;
        });
        $end = (int) $item->id;
        do {
            $this->factory->make(
                $request,
                collect($runner)->firstWhere("id", $counter),
                $this->pipeline,
                Str::random(18),
                $this->configRepository,
            );
            $continue = !($counter === $end);
            --$counter;

        }  while ($continue);

        $this->pipeline->handle();
    }

    /**
     * Handle incoming webhook requests dynamically with handler override support
     * Only processes if webhook handlers exist for this plugin
     * @throws \Exception
     * @throws GuzzleException
     */
    public function webhook(array $payload, string $endpoint = 'webhook'): array
    {
        try {
            // Check if this is a pipeline endpoint FIRST
            if ($this->isPipelineEndpoint($endpoint)) {
                return $this->executePipelineWebhook($payload, $endpoint);
            }

            $handlerClass = $this->determineHandlerClass($payload, $endpoint);

            if (!$handlerClass) {
                throw new \Exception("No webhook handler found for endpoint: {$endpoint}");
            }

            $pluginName = $this->configRepository->config->plugin_name ?? '';
            $pluginFolder = $this->mapPluginNameToWebhookFolder($pluginName);

            $fullHandlerClass = "\\App\\Plugins\\Webhooks\\{$pluginFolder}\\{$handlerClass}";

            \Log::info('Plugin webhook handler lookup', [
                'plugin_class' => get_class($this),
                'plugin_name' => $pluginName,
                'plugin_folder' => $pluginFolder,
                'endpoint' => $endpoint,
                'handler_class' => $handlerClass,
                'full_handler_class' => $fullHandlerClass,
                'class_exists' => class_exists($fullHandlerClass)
            ]);

            if (class_exists($fullHandlerClass)) {
                $handler = new $fullHandlerClass();
                $handler->register(
                    uri: $this->configRepository->getRoutePrefix(),
                    port: $this->configRepository->getPluginPort(),
                    routes: $this->configRepository->getPluginRoutes(),
                    tenant_id: $this->tenant_id ?? '',
                    tenant_name: $this->tenant_name ?? '',
                    hostname: $this->hostname ?? null,
                    configRepository: $this->configRepository
                );

                return $handler->handle($payload, $endpoint);
            }

            // Fallback to generic webhook
            return $this->handleGenericWebhook($payload, $endpoint);

        } catch (\Exception $e) {
            \Log::error('Plugin webhook processing failed', [
                'plugin' => get_class($this),
                'endpoint' => $endpoint,
                'error' => $e->getMessage(),
                'payload' => $payload
            ]);

            throw $e;
        }
    }

    /**
     * Check if endpoint has pipeline configuration
     */
    protected function isPipelineEndpoint(string $endpoint): bool
    {
        $webhookSettings = $this->configRepository->config->webhook_settings ?? [];

        foreach ($webhookSettings as $setting) {
            if (($setting['webhook_endpoint'] ?? '') === $endpoint) {
                return isset($setting['pipeline']) && is_array($setting['pipeline']) && !empty($setting['pipeline']);
            }
        }

        return false;
    }

    /**
     * Execute pipeline webhook
     */
    protected function executePipelineWebhook(array $payload, string $endpoint): array
    {
        $pluginFolder = $this->mapPluginNameToWebhookFolder($this->configRepository->config->plugin_name ?? '');

        // Create a generic pipeline handler or use a specific one
        $pipelineHandlerClass = "\\App\\Plugins\\Webhooks\\{$pluginFolder}\\BasePipelineHandler";

        // If no specific pipeline handler exists, use the base one dynamically
        if (!class_exists($pipelineHandlerClass)) {
            // Create anonymous class extending BaseWebhookPipelineHandler
            $handler = new class($this->configRepository) extends \App\Plugins\Webhooks\BaseWebhookPipelineHandler {
                protected function getPluginNameFromPluginManager(): string
                {
                    return 'DWD'; // or dynamically determine
                }
            };
        } else {
            $handler = new $pipelineHandlerClass();
        }

        $handler->register(
            uri: $this->configRepository->getRoutePrefix(),
            port: $this->configRepository->getPluginPort(),
            routes: $this->configRepository->getPluginRoutes(),
            tenant_id: $this->tenant_id ?? '',
            tenant_name: $this->tenant_name ?? '',
            hostname: $this->hostname ?? null,
            configRepository: $this->configRepository
        );

        return $handler->handle($payload, $endpoint);
    }

    /**
     * Determine handler class based on configuration and endpoint
     * Priority: route_handlers > webhook_settings > convention-based
     */
    protected function determineHandlerClass(array $payload, string $endpoint): ?string
    {
        // 1. Check route_handlers configuration first (highest priority)
        $routeHandlers = $this->configRepository->config->route_handlers ?? [];
        if (isset($routeHandlers[$endpoint])) {
            \Log::debug('Using route_handlers override', [
                'endpoint' => $endpoint,
                'handler' => $routeHandlers[$endpoint]
            ]);
            return $routeHandlers[$endpoint];
        }

        // 2. Check webhook_settings for specific endpoint + event combination
        $eventType = $payload['event_type'] ?? '';
        $modelType = $payload['model_type'] ?? '';

        $webhookSettings = $this->configRepository->config->webhook_settings ?? [];
        foreach ($webhookSettings as $setting) {
            $settingEndpoint = $setting['webhook_endpoint'] ?? '';
            $settingEvents = $setting['events'] ?? [];
            $settingModels = $setting['models'] ?? [];

            if ($settingEndpoint === $endpoint &&
                in_array($eventType, $settingEvents) &&
                in_array($modelType, $settingModels)) {

                // Check for both handler_class and target_class
                $handlerClass = $setting['handler_class'] ?? $setting['target_class'] ?? null;
                if ($handlerClass) {
                    \Log::debug('Using webhook_settings override', [
                        'endpoint' => $endpoint,
                        'event_type' => $eventType,
                        'model_type' => $modelType,
                        'handler' => $handlerClass
                    ]);
                    return $handlerClass;
                }
            }
        }

        // 3. Extract from endpoint (fallback to convention - original behavior)
        $handlerClass = $this->getHandlerFromEndpoint($endpoint);
        \Log::debug('Using convention-based handler', [
            'endpoint' => $endpoint,
            'handler' => $handlerClass
        ]);
        return $handlerClass;
    }

    /**
     * Extract handler class from endpoint using convention
     * Supports complex endpoints like "webhook/orders/bulk" -> "OrdersBulkWebhookHandler"
     */
    protected function getHandlerFromEndpoint(string $endpoint): ?string
    {
        if (preg_match('/webhook\/(\w+)(?:\/(\w+))?(?:\/(\w+))?/', $endpoint, $matches)) {
            $base = ucfirst($matches[1]); // "orders" -> "Orders"
            $action1 = isset($matches[2]) ? ucfirst($matches[2]) : ''; // "bulk" -> "Bulk"
            $action2 = isset($matches[3]) ? ucfirst($matches[3]) : ''; // "export" -> "Export"

            return $base . $action1 . $action2 . 'WebhookHandler';
        }

        return null;
    }

    /**
     * Handle generic webhooks - fallback method
     */
    protected function handleGenericWebhook(array $payload, string $endpoint): array
    {
        // Use model name for endpoint if available
        $modelType = $payload['model_type'] ?? null;
        if ($modelType) {
            $modelName = strtolower(class_basename($modelType));
            $endpoint = "webhook/{$modelName}";
        }

        return $this->makeRequest('POST', $endpoint, formParams: [
            'tenant_id' => $this->tenant_id,
            'data' => $payload,
            'timestamp' => now()->toISOString(),
        ], forceJson: true);
    }

    /**
     * Extract model name from endpoint (original method - kept for compatibility)
     */
    protected function getModelNameFromEndpoint(string $endpoint): ?string
    {
        // Extract from endpoint: "webhook/order" -> "Order"
        if (preg_match('/webhook\/(\w+)/', $endpoint, $matches)) {
            return ucfirst($matches[1]); // "order" -> "Order"
        }

        return null;
    }

    /**
     * Get model type from endpoint configuration
     */
    protected function getModelTypeFromEndpoint(string $endpoint): ?string
    {
        $webhookSettings = $this->configRepository->config->webhook_settings ?? [];

        foreach ($webhookSettings as $setting) {
            if (($setting['webhook_endpoint'] ?? '') === $endpoint) {
                return $setting['model_type'] ?? null;
            }
        }

        return null;
    }

    /**
     * Map plugin name to webhook folder name
     */
    protected function mapPluginNameToWebhookFolder(string $pluginName): string
    {
        // Handle different plugin name formats
        return match($pluginName) {
            'drukwerkdeal.nl' => 'DWD',
            'printcom' => 'Printcom',
            'print.com' => 'Printcom',
            // Add more mappings as needed
            default => \Str::studly(str_replace(['.', '-', '_'], '', $pluginName))
        };
    }

    /**
     * Check if this plugin has any webhook handlers configured
     */
    public function hasWebhookHandlers(): bool
    {
        $pluginName = $this->configRepository->config->plugin_name ?? '';
        $pluginFolder = $this->mapPluginNameToWebhookFolder($pluginName);
        $webhookPath = app_path("Plugins/Webhooks/{$pluginFolder}");

        return \File::exists($webhookPath) && !empty(\File::files($webhookPath));
    }
}
