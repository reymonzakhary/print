<?php

return [
    // Core application providers
    App\Providers\AppServiceProvider::class,
    App\Providers\AuthServiceProvider::class,
    App\Providers\TenancyServiceProvider::class, // <-- here
    App\Providers\EventServiceProvider::class,
    App\Providers\BroadcastServiceProvider::class,




    // Custom cache provider (if needed)
    App\Providers\CacheServiceProvider::class,

    // Business logic providers
    App\Providers\ShopServiceProvider::class,
    App\Providers\CartServiceProvider::class,

    // Feature providers
    App\Providers\BlueprintServiceProvider::class,
    App\Providers\PluginServiceProvider::class,
    App\Providers\WebhookQueueServiceProvider::class,

    // Domain-specific providers
    App\Foundation\ContractManager\Providers\ContractServiceProvider::class,
];
