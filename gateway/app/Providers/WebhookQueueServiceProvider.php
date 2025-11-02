<?php

namespace App\Providers;

use App\Console\Commands\WebhookEventsCommand;
use Illuminate\Support\ServiceProvider;
use App\Console\Commands\WebhookQueueStatusCommand;
use App\Console\Commands\WebhookQueueTestCommand;
use App\Console\Commands\WebhookQueueMetricsCommand;
use App\Console\Commands\WebhookQueueRebalanceCommand;
use App\Console\Commands\WebhookQueueClearCacheCommand;
use App\Plugins\WebhookQueueService;

class WebhookQueueServiceProvider extends ServiceProvider
{
    /**
     * Register services
     */
    public function register(): void
    {
        $this->app->singleton(WebhookQueueService::class);
    }

    /**
     * Bootstrap services
     */
    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                WebhookQueueStatusCommand::class,
                WebhookQueueTestCommand::class,
                WebhookQueueMetricsCommand::class,
                WebhookQueueRebalanceCommand::class,
                WebhookQueueClearCacheCommand::class,
                WebhookEventsCommand::class
            ]);
        }
    }
}
