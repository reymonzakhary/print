<?php

namespace App\Plugins\Traits;

use App\Enums\PluginStatus;
use App\Models\Hostname;
use App\Models\PluginWebhookEvent;
use App\Plugins\WebhookQueueService;
use Hyn\Tenancy\Facades\TenancyFacade;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

trait PluginWebhookTrait
{
    protected static function bootPluginWebhookTrait(): void
    {
        static::created(function ($model) {
            static::dispatchWebhookEvent($model, 'created');
        });

        static::updated(function ($model) {
            static::dispatchWebhookEvent($model, 'updated');
        });

        static::deleted(function ($model) {
            static::dispatchWebhookEvent($model, 'deleted');
        });


    }


    /**
     * Dispatch webhook event
     */
    protected static function dispatchWebhookEvent($model, string $event): void
    {
        if (!method_exists($model, 'shouldTriggerWebhook') || !$model->shouldTriggerWebhook($event)) {
            return;
        }

        $currentHostname = static::getCurrentTenantHostname();


        if (!$currentHostname) {
            \Log::debug('No tenant hostname found for webhook trigger');
            return;
        }



        $webhookConfig = static::getWebhookConfigForTenant($currentHostname, $model, $event);

        if ($webhookConfig) {
            $webhookEvent = static::createWebhookEventInSystemDatabase([
                'hostname_id' => $currentHostname->id,
                'model_type' => get_class($model),
                'model_id' => $model->id,
                'event_type' => $event,
                'payload' => array_merge($model->toArray(), [
                    'model_type' => get_class($model),
                    'model_id' => $model->id,
                    'event_type' => $event,
                ]),
                'plugin_config' => $webhookConfig,
                'status' => PluginStatus::PENDING,
                'attempts' => 0,
            ]);

            // Use the new WebhookQueueService for smart routing
            $queueService = app(WebhookQueueService::class);

            $options = [
                'priority' => static::getEventPriority($event, $model),
                'delay' => 10, // TODO Temporary fixed delay, can be made configurable
            ];

            $queueService->dispatch($webhookEvent, $currentHostname->id, $options);
        }
    }

    /**
     * Determine priority based on event type and model
     */
    protected static function getEventPriority(string $event, $model): int
    {
        // High priority events
        $highPriorityEvents = ['payment_completed', 'order_cancelled', 'urgent_notification'];
        if (in_array($event, $highPriorityEvents)) {
            return 9;
        }

        // Model-specific priority
        if (method_exists($model, 'getWebhookPriority')) {
            return $model->getWebhookPriority($event);
        }

        // Default priorities
        return match($event) {
            'created' => 6,
            'deleted' => 7,
            default => 5,
        };
    }

    // Keep existing methods unchanged...
    protected static function getCurrentTenantHostname(): ?Hostname
    {
        try {
            // First try to get from TenancyFacade (for HTTP requests)
            $website = TenancyFacade::website() ?? tenant();
            if ($website) {
                return $website->hostnames()->first();
            }
            return null;
        } catch (\Exception $e) {
            \Log::debug('Failed to get current tenant hostname', [
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    protected static function tenantSupportsWebhooks(Hostname $hostname): bool
    {
        if (!$hostname->website) {
            return false;
        }
        return $hostname->website->supplier
            && $hostname->website->external
            && $hostname->website->configure;
    }

    protected static function getWebhookConfigForTenant(Hostname $hostname, $model, string $event): ?array
    {
        try {
            $website = $hostname->website;
            $pluginConfig = $website->configure;

            $webhookSettings = $pluginConfig->webhook_settings ?? null;

            if ($webhookSettings) {
                $modelConfig = collect($webhookSettings)->firstWhere(function ($config) use ($model, $event) {
                    $models = $config['models'] ?? [];
                    $events = $config['events'] ?? [];
                    return in_array(get_class($model), $models) && in_array($event, $events);
                });

                if ($modelConfig) {
                    return array_merge($modelConfig, [
                        'plugin_name' => $pluginConfig->plugin_name,
                        'hostname_id' => $hostname->id,
                    ]);
                }
            }else{
                return null;
            }

            return static::getDefaultWebhookConfigForModel($pluginConfig, class_basename(get_class($model)), $hostname, $event);

        } catch (\Exception $e) {
            \Log::error('Failed to get webhook configuration for tenant', [
                'hostname_id' => $hostname->id,
                'model' => get_class($model),
                'event' => $event,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    protected static function getDefaultWebhookConfigForModel($pluginConfig, string $modelName, Hostname $hostname, string $event): array
    {
        $endpoint = 'webhook/' . strtolower($modelName);

        return [
            'plugin_name' => $pluginConfig->plugin_name,
            'hostname_id' => $hostname->id,
            'models' => [static::class],
            'events' => ['created', 'updated'],
            'webhook_endpoint' => $endpoint,
            'queue_mode' => 'background',
            'queue_name' => 'plugin-webhooks',
            'delay' => 0,
        ];
    }

    protected static function createWebhookEventInSystemDatabase(array $data): PluginWebhookEvent
    {
        $originalConnection = DB::getDefaultConnection();
        try {
            DB::setDefaultConnection('system');
            return PluginWebhookEvent::create($data);
        } finally {
            DB::setDefaultConnection($originalConnection);
        }
    }

    public function shouldTriggerWebhook(string $event): bool
    {
        return true;
    }

    /**
     * Override this in your models to set custom webhook priorities
     */
    public function getWebhookPriority(string $event): int
    {
        return 5; // Default priority
    }
}
