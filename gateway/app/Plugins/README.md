# Enterprise Plugin System with Multi-Queue Webhooks

A high-performance, multi-tenant plugin system for Laravel SaaS applications with intelligent webhook processing, priority-based queue routing, and enterprise-grade monitoring.

## Overview

This enterprise plugin system enables SaaS tenants to integrate with external services through a dynamic plugin architecture. Features automatic webhook processing with smart queue routing, priority handling, failover capabilities, and comprehensive monitoring for production environments.

## Architecture

```
app/Plugins/
├── Src/                          # Plugin managers for each service
│   ├── DwdPluginManager.php      # Drukwerkdeal.nl integration
│   └── PrintcomPluginManager.php # Printcom integration
├── Webhooks/                     # Smart webhook handlers
│   ├── BaseWebhookHandler.php    # Abstract base with routing logic
│   ├── DWD/                      # Drukwerkdeal handlers
│   │   ├── OrderWebhookHandler.php
│   │   └── QuotationWebhookHandler.php
│   └── Printcom/                 # Printcom handlers
│       └── OrderWebhookHandler.php
├── Config/                       # Configuration management
│   ├── PluginConfigRepository.php
│   └── DefaultConfigRepository.php
├── Jobs/                         # Queue job processing
│   └── PluginWebhookJob.php
├── Traits/                       # Model integration
│   └── PluginWebhookTrait.php
├── Contracts/                    # Base contracts
│   └── PluginManager.php
├── WebhookQueueService.php       # Enterprise queue routing
└── Console/Commands/             # Management commands
    ├── WebhookQueueStatusCommand.php
    ├── WebhookQueueTestCommand.php
    ├── WebhookQueueMetricsCommand.php
    ├── WebhookQueueRebalanceCommand.php
    └── WebhookQueueClearCacheCommand.php
```

## Enterprise Queue System

### 6-Tier Queue Architecture

The system uses intelligent routing across multiple queue drivers with automatic failover:

- **webhook-high** (Beanstalkd) - Critical operations, 2 workers
- **webhook-beanstalk** (Beanstalkd) - Fast processing, 3 workers
- **webhook-redis** (Redis) - Immediate responses, 2 workers
- **webhook-db** (Database) - Reliable fallback, 2 workers
- **webhook-low** (Database) - Bulk operations, 1 worker
- **webhook-failed** (Database) - Failed job retries, 1 worker

### Smart Priority Routing

Jobs are automatically routed based on:
- **Priority Level**: 1-10 scale with intelligent defaults
- **Delay Requirements**: Immediate vs scheduled processing
- **Event Type**: Critical vs routine operations
- **Queue Health**: Automatic failover to healthy queues
- **Load Balancing**: Even distribution across available drivers

## Installation

### 1. Dependencies

```bash
composer require predis/predis pda/pheanstalk
```

### 2. Database Setup

```bash
php artisan queue:table
php artisan queue:failed-table
php artisan migrate
```

### 3. Queue Configuration

Update `config/queue.php` with enterprise webhook queues:

```php
'connections' => [
    // Existing connections...
    
    'webhook-high' => [
        'driver' => 'beanstalkd',
        'host' => env('BEANSTALKD_HOST', 'localhost'),
        'queue' => 'plugin-webhooks-high',
        'retry_after' => 60,
    ],
    'webhook-beanstalk' => [
        'driver' => 'beanstalkd', 
        'host' => env('BEANSTALKD_HOST', 'localhost'),
        'queue' => 'plugin-webhooks-tube',
        'retry_after' => 90,
    ],
    'webhook-redis' => [
        'driver' => 'redis',
        'connection' => 'default',
        'queue' => 'plugin-webhooks-fast',
        'retry_after' => 90,
    ],
    'webhook-db' => [
        'driver' => 'database',
        'table' => 'jobs',
        'queue' => 'plugin-webhooks',
        'connection' => 'system',
        'retry_after' => 90,
    ],
    'webhook-low' => [
        'driver' => 'database',
        'table' => 'jobs', 
        'queue' => 'plugin-webhooks-low',
        'connection' => 'system',
        'retry_after' => 600,
    ],
    'webhook-failed' => [
        'driver' => 'database',
        'table' => 'jobs',
        'queue' => 'plugin-webhooks-failed', 
        'connection' => 'system',
        'retry_after' => 1800,
    ],
],

'webhook_routing' => [
    'high_priority' => [
        'drivers' => ['webhook-high', 'webhook-beanstalk', 'webhook-redis'],
        'conditions' => [
            'priority' => ['>=', 8],
            'delay' => ['<', 60],
            'event_types' => ['payment_completed', 'order_cancelled', 'urgent_notification'],
        ],
    ],
    'default' => [
        'drivers' => ['webhook-beanstalk', 'webhook-redis', 'webhook-db'],
        'conditions' => [
            'priority' => ['between', [3, 7]],
            'delay' => ['<=', 300],
            'event_types' => ['order_created', 'order_updated', 'quotation_created'],
        ],
    ],
    'low_priority' => [
        'drivers' => ['webhook-low', 'webhook-db'],
        'conditions' => [
            'priority' => ['<', 3],
            'delay' => ['>', 300],
            'event_types' => ['data_sync', 'report_generation', 'cleanup'],
        ],
    ],
],
```

### 4. Environment Variables

```env
# Queue Configuration
QUEUE_CONNECTION=database
QUEUE_FAILED_DRIVER=database

# Webhook Queue Settings
WEBHOOK_DB_QUEUE=plugin-webhooks
WEBHOOK_REDIS_QUEUE=plugin-webhooks-fast
WEBHOOK_BEANSTALK_QUEUE=plugin-webhooks-tube
BEANSTALKD_HOST=127.0.0.1

# Monitoring
WEBHOOK_QUEUE_MONITORING=true
```

### 5. Supervisor Configuration

```ini
# High Priority Workers
[program:webhook-high-worker]
command=php /var/www/artisan queue:work webhook-high --sleep=1 --tries=3
numprocs=2

# Beanstalk Workers  
[program:webhook-beanstalk-worker]
command=php /var/www/artisan queue:work webhook-beanstalk --sleep=2 --tries=3
numprocs=3

# Redis Workers
[program:webhook-redis-worker] 
command=php /var/www/artisan queue:work webhook-redis --sleep=2 --tries=3
numprocs=2

# Database Workers
[program:webhook-db-worker]
command=php /var/www/artisan queue:work webhook-db --sleep=3 --tries=3
numprocs=2

# Low Priority Workers
[program:webhook-low-worker]
command=php /var/www/artisan queue:work webhook-low --sleep=5 --tries=5
numprocs=1

# Failed Job Workers
[program:webhook-failed-worker]
command=php /var/www/artisan queue:work webhook-failed --sleep=10 --tries=5
numprocs=1

[group:webhook-workers]
programs=webhook-high-worker,webhook-beanstalk-worker,webhook-redis-worker,webhook-db-worker,webhook-low-worker,webhook-failed-worker
```

### 6. Service Provider Registration

Add to `config/app.php`:

```php
'providers' => [
    // ...
    App\Providers\WebhookQueueServiceProvider::class,
],
```

## Configuration

### Three Webhook Trigger Methods

The system supports three distinct webhook processing approaches:

#### 1. Model-Based Triggers (Automatic)
Triggered by model events (create/update/delete) using the `PluginWebhookTrait`.

#### 2. Route-Based Triggers (Manual)
Custom endpoint routing with specific handlers.

#### 3. Pipeline Triggers (Sequential)
Multi-step processing with data passing between handlers.

### Tenant Plugin Configuration

Enterprise configuration stored in `websites.configure`:

```json
{
    "auth": {
        "url": "https://api.drukwerkdeal.nl",
        "secret": "your-secret-key",
        "client_id": "your-client-id"
    },
    "plugin__v": "2.0.0",
    "plugin_name": "drukwerkdeal.nl",
    "plugin_port": 5000,
    "plugin_routes": [
        {"route": "categories", "method": "GET"},
        {"route": "orders", "method": "POST"},
        {"route": "webhook/order", "method": "POST"},
        {"route": "webhook/quotation", "method": "POST"}
    ],
    "plugin_route_prefix": "dwd",
    
    // Route-based handlers (highest priority)
    "route_handlers": {
        "webhook/order/export": "OrderExportHandler",
        "webhook/order/bulk": "OrderBulkHandler",
        "webhook/custom/special": "SpecialOrderWebhookHandler"
    },
    
    // Model-based and pipeline configurations
    "webhook_settings": [
        {
            // Model-based with custom target_class
            "models": ["App\\Models\\Tenants\\Order"],
            "events": ["created", "updated"],
            "webhook_endpoint": "webhook/order",
            "target_class": "OrderWebhookHandler", // Optional - defaults to convention
            "queue_mode": "background",
            "priority": 7,
            "delay": 0
        },
        {
            // Model-based without target_class (uses convention)
            "models": ["App\\Models\\Tenants\\Quotation"],
            "events": ["created", "updated"],
            "webhook_endpoint": "webhook/quotation", // -> QuotationWebhookHandler
            "queue_mode": "background", 
            "priority": 5,
            "delay": 60
        },
        {
            // Pipeline processing
            "webhook_endpoint": "webhook/order/process",
            "pipeline": [
                {
                    "handler_class": "OrderValidationHandler",
                    "retry_attempts": 3,
                    "timeout": 30
                },
                {
                    "handler_class": "OrderTransformHandler",
                    "retry_attempts": 2,
                    "timeout": 60
                },
                {
                    "handler_class": "OrderSubmitHandler",
                    "retry_attempts": 5,
                    "timeout": 120
                }
            ]
        }
    ]
}
```

## Usage Examples

### Basic Plugin Operations

```php
// Load plugin for current tenant
$pluginService = app(\App\Plugins\PluginService::class)->load();

// Load for specific tenant
$pluginService->load($hostname); // Hostname model or ID

// Call plugin methods
$categories = $pluginService->getCategories();
$prices = $pluginService->getPrice($quantity, $productData);
$syncResult = $pluginService->getSyncPipelineConfig();
```

## Webhook Trigger Methods

### 1. Model-Based Automatic Webhooks

Webhooks triggered automatically by model events (create/update/delete):

```php
use App\Plugins\Traits\PluginWebhookTrait;

class Order extends Model
{
    use PluginWebhookTrait;

    protected $fillable = ['customer_id', 'total', 'status', 'items'];

    /**
     * Dynamic priority based on order characteristics
     */
    public function getWebhookPriority(string $event): int
    {
        return match($event) {
            'created' => ($this->total ?? 0) > 1000 ? 8 : 6,
            'updated' => $this->status === 'cancelled' ? 9 : 5,
            'deleted' => 7,
            default => 5,
        };
    }

    /**
     * Control when webhooks fire
     */
    public function shouldTriggerWebhook(string $event): bool
    {
        if ($event === 'updated') {
            // Only trigger on important changes
            return $this->wasChanged(['status', 'total', 'shipping_address']);
        }
        
        return true;
    }
}

// Usage - webhooks trigger automatically
$order = Order::create([
    'customer_id' => 456,
    'total' => 1500.00,  // High value = priority 8 = fast queue
    'status' => 'pending'
]);
// -> Triggers webhook/order -> OrderWebhookHandler (if target_class set)
//    OR webhook/order -> convention-based handler

$order->update(['status' => 'cancelled']); // Priority 9 = immediate processing
```

### 2. Manual Route-Based Webhooks

Manual triggers with custom endpoints and specific handlers:

```php
// Route to custom handler
$success = $pluginService->triggerWebhook(
    endpoint: 'webhook/order/export',  // -> OrderExportHandler (from route_handlers)
    payload: [
        'order_ids' => [123, 124, 125],
        'format' => 'xlsx',
        'include_items' => true
    ],
    eventType: 'bulk_export',
    async: true,
    options: [
        'priority' => 7,
        'delay' => 0
    ]
);

// Another custom route
$success = $pluginService->triggerWebhook(
    endpoint: 'webhook/custom/special',  // -> SpecialOrderWebhookHandler
    payload: ['special_operation' => 'sync_external'],
    eventType: 'custom_sync'
);
```

### 3. Pipeline Processing Webhooks

Sequential multi-handler processing with data passing:

```php
// Trigger pipeline processing
$success = $pluginService->triggerWebhook(
    endpoint: 'webhook/order/process',  // -> Pipeline with 3 handlers
    payload: [
        'order_id' => 123,
        'items' => [
            ['sku' => 'ABC123', 'quantity' => 2, 'specs' => ['color' => 'red']],
            ['sku' => 'XYZ789', 'quantity' => 1, 'specs' => ['size' => 'large']]
        ],
        'customer_id' => 456,
        'processing_options' => [
            'validate_inventory' => true,
            'apply_discounts' => true,
            'send_confirmation' => true
        ]
    ],
    eventType: 'pipeline_processing',
    async: true,
    options: [
        'priority' => 8,  // High priority for complex processing
        'delay' => 0
    ]
);
```

### Disable Default Behavior for Custom Control

For complex scenarios with related models where you need full control:

```php
class Order extends Model
{
    use PluginWebhookTrait;

    /**
     * Disable default webhook behavior - use custom logic only
     */
    public function shouldTriggerWebhook(string $event): bool
    {
        return false; // Disable all default webhooks
    }

    /**
     * Custom webhook triggering logic
     */
    protected static function bootPluginWebhookTrait(): void
    {
        static::created(function ($order) {
            // Only trigger after all related models are created
            $order->triggerCustomWebhooksAfterCreation();
        });

        static::updated(function ($order) {
            if ($order->shouldTriggerCustomUpdate()) {
                $order->triggerCustomWebhook('order_updated');
            }
        });
    }

    /**
     * Trigger custom webhooks after order and items are fully created
     */
    public function triggerCustomWebhooksAfterCreation()
    {
        \DB::afterCommit(function () {
            $this->refresh(); // Ensure we have latest data with relations
            
            // Check if order is complete with items
            if ($this->orderItems()->count() === 0) {
                \Log::info('Order created without items, skipping webhook');
                return;
            }

            // Determine which handler to use based on order characteristics
            $handlerType = $this->determineWebhookHandler();
            
            switch ($handlerType) {
                case 'pipeline':
                    $this->triggerPipelineWebhook();
                    break;
                case 'custom':
                    $this->triggerCustomWebhook('order_created_complete');
                    break;
                case 'bulk':
                    $this->triggerBulkWebhook();
                    break;
            }
        });
    }

    protected function determineWebhookHandler(): ?string
    {
        if ($this->orderItems()->count() > 10) {
            return 'bulk'; // Use bulk handler for large orders
        }

        if ($this->total > 1000 || $this->hasComplexItems()) {
            return 'pipeline'; // Use pipeline for complex orders
        }

        if ($this->status === 'confirmed') {
            return 'custom'; // Use custom handler for standard orders
        }

        return null; // Skip webhook
    }

    protected function triggerPipelineWebhook()
    {
        $pluginService = app(\App\Plugins\PluginService::class)->load();
        
        $pluginService->triggerWebhook(
            endpoint: 'webhook/order/process',
            payload: $this->buildWebhookPayload(),
            eventType: 'order_pipeline_processing',
            async: true,
            options: ['priority' => 8, 'delay' => 0]
        );
    }

    protected function buildWebhookPayload(): array
    {
        return [
            'order' => $this->toArray(),
            'items' => $this->orderItems()->with('item')->get()->toArray(),
            'customer' => $this->customer?->toArray(),
            'metadata' => [
                'total_items' => $this->orderItems()->count(),
                'created_complete_at' => now()->toISOString(),
            ]
        ];
    }
}
```

### Advanced Pipeline Handler Example

```php
// app/Plugins/Webhooks/DWD/OrderValidationHandler.php
namespace App\Plugins\Webhooks\DWD;

use App\Plugins\Webhooks\BaseWebhookHandler;

class OrderValidationHandler extends BaseWebhookHandler
{
    public function handle(array $payload, string $endpoint): array
    {
        return $this->handleStep($payload, 0);
    }

    public function handleStep(array $data, int $step): array
    {
        // Validate order data
        if (!isset($data['order_id'])) {
            throw new \Exception("Order ID is required");
        }

        // Validate items
        foreach ($data['items'] as $item) {
            if (!isset($item['sku']) || !isset($item['quantity'])) {
                throw new \Exception("Invalid item format");
            }
        }

        return [
            'validation_status' => 'passed',
            'validated_at' => now()->toISOString(),
            'rules_checked' => ['order_id', 'items', 'sku', 'quantity']
        ];
    }
}

// app/Plugins/Webhooks/DWD/OrderTransformHandler.php  
class OrderTransformHandler extends BaseWebhookHandler
{
    public function handleStep(array $data, int $step): array
    {
        // Use results from previous step
        $validationResult = $data['previous_step_result'];
        
        // Transform data for external API
        $transformedItems = [];
        foreach ($data['items'] as $item) {
            $transformedItems[] = [
                'external_sku' => $this->mapSku($item['sku']),
                'qty' => $item['quantity'],
                'specifications' => $this->mapSpecifications($item),
            ];
        }

        return [
            'external_order' => [
                'ref' => "TENANT-{$this->tenant_id}-{$data['order_id']}",
                'items' => $transformedItems,
                'validation' => $validationResult,
            ]
        ];
    }
}
```

### Handler Resolution Priority

The system resolves handlers in this priority order:

1. **Pipeline Configuration** - If `pipeline` array exists
2. **Route Handlers** - If endpoint exists in `route_handlers`
3. **Webhook Settings with target_class** - If `target_class` specified
4. **Convention-Based** - Extract handler from endpoint pattern

```php
// Examples of handler resolution:

// 1. Pipeline (highest priority)
// webhook/order/process -> Pipeline with OrderValidationHandler, OrderTransformHandler, OrderSubmitHandler

// 2. Route handlers
// webhook/order/export -> OrderExportHandler (from route_handlers config)

// 3. Custom target_class
// webhook/order -> CustomOrderHandler (from webhook_settings target_class)

// 4. Convention-based (lowest priority)
// webhook/order -> OrderWebhookHandler (convention)
// webhook/orders/bulk -> OrdersBulkWebhookHandler (convention)
```

## Management Commands

### Queue Monitoring

```bash
# Check queue health and load
php artisan webhook:status
php artisan webhook:status --json

# Test all queue connections
php artisan webhook:test

# View processing metrics  
php artisan webhook:metrics --days=7

# Rebalance overloaded queues
php artisan webhook:rebalance
php artisan webhook:rebalance --dry-run

# Clear metrics cache
php artisan webhook:clear-cache

# Show recent webhook events
php artisan webhook:events --limit=20
```

### Supervisor Management

```bash
# Check webhook workers
supervisorctl status webhook-workers:*

# Restart all webhook workers
supervisorctl restart webhook-workers:*

# Start/stop specific priority workers
supervisorctl start webhook-workers:webhook-high-worker_*
supervisorctl stop webhook-workers:webhook-low-worker_*
```

### Queue Statistics

```bash
# View queue depths
php artisan queue:work --once webhook-high
php artisan queue:work --once webhook-redis  

# Check failed jobs
php artisan queue:failed
php artisan queue:retry all
```

## Monitoring & Performance

### Real-time Metrics

```php
// Get queue health status
$queueService = app(\App\Plugins\WebhookQueueService::class);
$health = $queueService->getQueueHealth();

foreach ($health as $queue => $status) {
    echo "{$queue}: {$status['status']} (Load: {$status['load']})\n";
}

// Get processing metrics
$metrics = $queueService->getQueueMetrics(7); // Last 7 days
```

### Automated Monitoring

```bash
# Add to crontab for automated monitoring
*/5 * * * * /usr/local/bin/webhook-monitor.sh

# webhook-monitor.sh content:
#!/bin/bash
UNHEALTHY=$(php /var/www/artisan webhook:status --json | jq -r 'to_entries[] | select(.value.status == "unhealthy") | .key')

if [ ! -z "$UNHEALTHY" ]; then
    echo "ALERT: Unhealthy webhook queues: $UNHEALTHY"
    # Send alert (email, Slack, etc.)
fi

# Auto-rebalance every 6 hours
0 */6 * * * php /var/www/artisan webhook:rebalance >> /var/log/webhook-rebalance.log
```

### Performance Optimization

**Queue Worker Scaling:**
- High-priority queues: 2-3 workers
- Medium-priority queues: 2-4 workers
- Low-priority queues: 1-2 workers
- Scale based on queue depth and processing times

**Database Cleanup:**
```php
// Clean old webhook events (30+ days)
PluginWebhookEvent::where('created_at', '<', now()->subDays(30))->delete();
```

**Memory Management:**
```bash
# Set memory limits for workers
php artisan queue:work webhook-high --memory=512
```

## Security

### Authentication Security

```php
// Encrypt sensitive plugin configurations
$config = encrypt(json_encode($pluginCredentials));
Website::where('id', $websiteId)->update(['configure' => $config]);

// Access with decryption
$configure = decrypt($website->configure);
```

### Webhook Validation

```php
protected function validateWebhookPayload(array $payload, string $endpoint): bool
{
    // Validate signature (if required by external service)
    if (!$this->verifyWebhookSignature($payload, $this->getSecret())) {
        return false;
    }
    
    // Validate required fields
    $required = ['id', 'event_type', 'tenant_id'];
    foreach ($required as $field) {
        if (!isset($payload[$field])) {
            return false;
        }
    }
    
    // Validate data types and ranges
    if (isset($payload['total']) && (!is_numeric($payload['total']) || $payload['total'] < 0)) {
        return false;
    }
    
    return true;
}
```

## Troubleshooting

### Common Issues

**Queue Workers Not Processing:**
```bash
# Check if workers are running
ps aux | grep "queue:work"

# Restart workers
supervisorctl restart webhook-workers:*

# Check queue depth
php artisan webhook:status
```

**High Memory Usage:**
```bash
# Restart workers periodically
php artisan queue:restart

# Monitor memory usage
php artisan queue:work --memory=256 --timeout=300
```

**Failed Jobs Accumulating:**
```bash
# Check failed jobs
php artisan queue:failed

# Retry specific jobs
php artisan queue:retry 5

# Clear old failed jobs  
php artisan queue:flush
```

### Debug Mode

```php
// Enable verbose logging in webhook handlers
Log::debug('DWD webhook processing', [
    'tenant_id' => $this->tenant_id,
    'payload' => $payload,
    'endpoint' => $endpoint,
    'queue_connection' => config('queue.default'),
]);
```

## Production Deployment

### Checklist

- [ ] Queue workers configured with Supervisor
- [ ] Database connections optimized for load
- [ ] Redis/Beanstalkd properly configured
- [ ] Monitoring scripts scheduled in crontab
- [ ] Log rotation configured
- [ ] Backup strategy includes webhook event data
- [ ] Error alerting configured (email/Slack)
- [ ] Performance baselines established

### Scaling Guidelines

**Small SaaS (< 1000 webhooks/hour):**
- 2 Beanstalk workers, 1 Redis worker, 1 DB worker

**Medium SaaS (1000-10000 webhooks/hour):**
- 3 Beanstalk workers, 2 Redis workers, 2 DB workers

**Large SaaS (10000+ webhooks/hour):**
- 5 Beanstalk workers, 3 Redis workers, 3 DB workers
- Consider Redis Cluster and database read replicas
- Implement queue worker auto-scaling

This enterprise webhook system provides production-ready reliability, intelligent routing, and comprehensive monitoring for high-scale SaaS applications.
