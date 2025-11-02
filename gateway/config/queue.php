<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Queue Connection Name
    |--------------------------------------------------------------------------
    */

    'default' => env('QUEUE_CONNECTION', 'database'),

    /*
    |--------------------------------------------------------------------------
    | Queue Connections
    |--------------------------------------------------------------------------
    */

    'connections' => [

        'sync' => [
            'driver' => 'sync',
        ],

        // Your existing database connection
        'database' => [
            'driver' => 'database',
            'table' => 'jobs',
            'queue' => 'default',
            'connection' => 'system',
            'retry_after' => 600,
        ],

        // Your existing blueprint connection
        'blueprint' => [
            'driver' => 'database',
            'table' => 'jobs',
            'queue' => 'high',
            'connection' => 'tenant',
            'retry_after' => 5,
        ],

        // Your existing connections
        'beanstalkd' => [
            'driver' => 'beanstalkd',
            'host' => 'localhost',
            'queue' => 'default',
            'retry_after' => 90,
            'block_for' => 0,
        ],

        'sqs' => [
            'driver' => 'sqs',
            'key' => env('AWS_ACCESS_KEY_ID'),
            'secret' => env('AWS_SECRET_ACCESS_KEY'),
            'prefix' => env('SQS_PREFIX', 'https://sqs.us-east-1.amazonaws.com/your-account-id'),
            'queue' => env('SQS_QUEUE', 'your-queue-name'),
            'suffix' => env('SQS_SUFFIX'),
            'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
        ],

        'redis' => [
            'driver' => 'redis',
            'connection' => 'default',
            'queue' => env('REDIS_QUEUE', 'default'),
            'retry_after' => 600,
            'block_for' => null,
            'after_commit' => true,
        ],

        // Enterprise Webhook Queue System
        'webhook-high' => [
            'driver' => 'beanstalkd',
            'host' => env('BEANSTALKD_HOST', 'localhost'),
            'queue' => 'plugin-webhooks-high',
            'retry_after' => 60,
            'block_for' => 0,
            'after_commit' => false,
        ],

        'webhook-beanstalk' => [
            'driver' => 'beanstalkd',
            'host' => env('BEANSTALKD_HOST', 'localhost'),
            'queue' => env('WEBHOOK_BEANSTALK_QUEUE', 'plugin-webhooks-tube'),
            'retry_after' => 90,
            'block_for' => 0,
            'after_commit' => false,
        ],

        'webhook-redis' => [
            'driver' => 'redis',
            'connection' => 'default',
            'queue' => env('WEBHOOK_REDIS_QUEUE', 'plugin-webhooks-fast'),
            'retry_after' => 90,
            'block_for' => null,
            'after_commit' => false,
        ],

        'webhook-db' => [
            'driver' => 'database',
            'table' => 'jobs',
            'queue' => env('WEBHOOK_DB_QUEUE', 'plugin-webhooks'),
            'connection' => 'system',
            'retry_after' => 90,
            'after_commit' => false,
        ],

        'webhook-low' => [
            'driver' => 'database',
            'table' => 'jobs',
            'queue' => 'plugin-webhooks-low',
            'connection' => 'system',
            'retry_after' => 600,
            'after_commit' => false,
        ],

        'webhook-failed' => [
            'driver' => 'database',
            'table' => 'jobs',
            'queue' => 'plugin-webhooks-failed',
            'connection' => 'system',
            'retry_after' => 1800,
            'after_commit' => false,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Webhook Queue Routing Rules
    |--------------------------------------------------------------------------
    */
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
        'failed_retry' => [
            'drivers' => ['webhook-failed'],
            'conditions' => [
                'attempts' => ['>', 3],
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Failed Queue Jobs
    |--------------------------------------------------------------------------
    */
    'failed' => [
        'driver' => env('QUEUE_FAILED_DRIVER', 'database'),
        'database' => env('TENANCY_DEFAULT_CONNECTION', 'tenant'),
        'connection' => 'tenant',
        'table' => 'failed_jobs',
    ],

];
