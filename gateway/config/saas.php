<?php

return [
    'trial_days' => 14,

    'plans' => [
        'price_1Jg5QKGlrejN28VyHdwwSIrD' => 'Pro — $10',
        'price_1Jg5QKGlrejN28VyCaMaTd7X' => 'Premium — $20',
    ],

    'cancelation_reasons' => [
        'Too expensive',
        'Lacks features',
        'Not what I expected',
    ],

    'stripe_key' => env('STRIPE_KEY'),
    'stripe_secret' => env('STRIPE_SECRET'),

    // Subdomains that tenants may not use for their domains
    // You can use common keywords here, like blog, app, docs, ...
    'reserved_subdomains' => [],
];
