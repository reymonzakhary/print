<?php

namespace App\Plugins\Webhooks;

use App\Plugins\Contracts\PluginManager;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Str;

/**
 * Handle incoming webhook requests dynamically
 * @author Reymon Zakhary
 * @param array $payload The payload data received from the webhook
 * @param string $endpoint The endpoint to process the webhook for
 * @return array The response data after processing the webhook
 * @throws \Exception When webhook processing fails
 * @throws GuzzleException
 */
abstract class BaseWebhookHandler extends PluginManager
{
    /**
     * Handle incoming webhook requests dynamically based on model type
     *
     * @param array $payload The payload data received from the webhook
     * @param string $endpoint The endpoint to process the webhook for
     * @return array The response data after processing the webhook
     * @throws \Exception When webhook processing fails
     * @throws GuzzleException
     */
    public function webhook(array $payload, string $endpoint = 'webhook'): array
    {
        try {
            // Find handler based on model type from payload
            $handler = $this->findHandlerByModelType($payload);

            if ($handler && method_exists($handler, 'handle')) {
                return $handler->handle($payload, $endpoint);
            }

            // Fallback to generic webhook if no specific handler found
            return $this->handleGenericWebhook($payload, $endpoint);

        } catch (\Exception $e) {
            \Log::error('Webhook processing failed', [
                'plugin' => get_class($this),
                'model_type' => $payload['model_type'] ?? 'unknown',
                'error' => $e->getMessage(),
                'payload' => $payload
            ]);

            throw $e;
        }
    }

    /**
     * Find webhook handler based on model type in payload
     */
    protected function findHandlerByModelType(array $payload): ?BaseWebhookHandler
    {
        // Extract model type from payload
        $modelType = $payload['model_type'] ?? null;

        if (!$modelType) {
            \Log::warning('No model_type found in webhook payload', ['payload' => $payload]);
            return null;
        }

        // Get model name (e.g., "Order" from "App\Models\Order")
        $modelName = class_basename($modelType);

        // Get current plugin name
        $pluginName = $this->getPluginNameFromPluginManager();

        // Build handler class name: {ModelName}WebhookHandler
        $handlerName = $modelName . 'WebhookHandler';
        $handlerClass = "\\App\\Plugins\\Webhooks\\{$pluginName}\\{$handlerName}";

        if (class_exists($handlerClass)) {
            $handler = new $handlerClass();

            // Copy current configuration to the handler
            $handler->register(
                uri: $this->base_uri ?? '',
                port: $this->port ?? 0,
                routes: $this->routes ?? [],
                tenant_id: $this->tenant_id ?? '',
                tenant_name: $this->tenant_name ?? '',
                hostname: $this->hostname ?? null,
                configRepository: $this->configRepository ?? null
            );

            return $handler;
        }

        \Log::info('No specific handler found for model', [
            'model_type' => $modelType,
            'expected_handler' => $handlerClass,
            'plugin' => $pluginName
        ]);

        return null;
    }

    /**
     * Get plugin name from the main plugin manager class
     */
    protected function getPluginNameFromPluginManager(): string
    {
        $className = class_basename(get_class($this));
        return Str::before($className, 'PluginManager');
    }

    /**
     * Handle generic webhooks - fallback method
     * @throws GuzzleException
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
     * Process webhook data and transform if needed
     */
    protected function transformWebhookData(array $payload, string $type): array
    {
        // Override this method in specific webhook handlers to transform data
        return $payload;
    }

    /**
     * Validate webhook payload
     */
    protected function validateWebhookPayload(array $payload, string $endpoint): bool
    {
        // Basic validation - override in specific webhook handlers for custom validation
        return !empty($payload);
    }

    /**
     * Abstract method that must be implemented by specific webhook handlers
     */
    abstract public function handle(array $payload, string $endpoint): array;
}
