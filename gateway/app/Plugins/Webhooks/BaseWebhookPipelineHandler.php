<?php

namespace App\Plugins\Webhooks;

use Exception;
// test for now
abstract class BaseWebhookPipelineHandler  extends BaseWebhookHandler
{
    /**
     * @throws Exception
     */
    public function handle(array $payload, string $endpoint): array
    {
        $pipelineConfig = $this->getPipelineConfig($endpoint);

        if (empty($pipelineConfig)) {
            throw new Exception("No pipeline configuration found for endpoint: {$endpoint}");
        }

        return $this->executePipeline($payload, $pipelineConfig, $endpoint);
    }

    /**
     * Execute the pipeline sequentially
     * @throws Exception
     */
    protected function executePipeline(array $payload, array $pipeline, string $endpoint): array
    {
        $currentData = $payload;
        $pipelineResults = [];

        foreach ($pipeline as $step => $config) {
            $handlerClass = $config['handler_class'];
            $retryAttempts = $config['retry_attempts'] ?? 3;
            $timeout = $config['timeout'] ?? 60;

            \Log::info("Executing pipeline step {$step}", [
                'handler' => $handlerClass,
                'tenant_id' => $this->tenant_id,
            ]);

            try {
                $result = $this->executePipelineStep(
                    $handlerClass,
                    $currentData,
                    $retryAttempts,
                    $timeout,
                    $step
                );

                $pipelineResults[$step] = [
                    'handler' => $handlerClass,
                    'success' => true,
                    'result' => $result,
                    'timestamp' => now()->toISOString()
                ];

                // Pass result to next step
                $currentData = array_merge($currentData, [
                    'previous_step_result' => $result,
                    'pipeline_results' => $pipelineResults,
                    'current_step' => $step + 1
                ]);

            } catch (Exception $e) {
                $pipelineResults[$step] = [
                    'handler' => $handlerClass,
                    'success' => false,
                    'error' => $e->getMessage(),
                    'timestamp' => now()->toISOString()
                ];

                // Stop pipeline on failure
                throw new Exception("Pipeline failed at step {$step}: {$e->getMessage()}");
            }
        }

        return [
            'success' => true,
            'pipeline_results' => $pipelineResults,
            'final_data' => $currentData,
            'completed_at' => now()->toISOString()
        ];
    }

    /**
     * Execute a single pipeline step with retry logic
     * @throws Exception
     */
    protected function executePipelineStep(
        string $handlerClass,
        array $data,
        int $retryAttempts,
        int $timeout,
        int $step
    ): array {
        $pluginFolder = $this->getPluginNameFromPluginManager();
        $fullHandlerClass = "\\App\\Plugins\\Webhooks\\{$pluginFolder}\\{$handlerClass}";

        if (!class_exists($fullHandlerClass)) {
            throw new Exception("Pipeline handler not found: {$fullHandlerClass}");
        }

        $attempt = 0;
        $lastException = null;

        while ($attempt < $retryAttempts) {
            try {
                $attempt++;

                $handler = new $fullHandlerClass();
                $handler->register(
                    uri: $this->base_uri ?? '',
                    port: $this->port ?? 0,
                    routes: $this->routes ?? [],
                    tenant_id: $this->tenant_id ?? '',
                    tenant_name: $this->tenant_name ?? '',
                    hostname: $this->hostname ?? null,
                    configRepository: $this->configRepository ?? null
                );

                // Set timeout
                set_time_limit($timeout);

                // Execute the step
                $result = $handler->handleStep($data, $step);

                \Log::info("Pipeline step completed", [
                    'handler' => $handlerClass,
                    'step' => $step,
                    'attempt' => $attempt,
                    'tenant_id' => $this->tenant_id
                ]);

                return $result;

            } catch (Exception $e) {
                $lastException = $e;

                \Log::warning("Pipeline step failed", [
                    'handler' => $handlerClass,
                    'step' => $step,
                    'attempt' => $attempt,
                    'error' => $e->getMessage(),
                    'tenant_id' => $this->tenant_id
                ]);

                if ($attempt < $retryAttempts) {
                    // Wait before retry (exponential backoff)
                    sleep(min(pow(2, $attempt), 30));
                }
            }
        }

        throw new Exception(
            "Pipeline step failed after {$retryAttempts} attempts: " .
            $lastException->getMessage()
        );
    }

    /**
     * Get pipeline configuration for endpoint
     */
    protected function getPipelineConfig(string $endpoint): array
    {
        $webhookSettings = $this->configRepository->config->webhook_settings ?? [];

        foreach ($webhookSettings as $setting) {
            if (($setting['webhook_endpoint'] ?? '') === $endpoint) {
                return $setting['pipeline'] ?? [];
            }
        }

        return [];
    }
}
