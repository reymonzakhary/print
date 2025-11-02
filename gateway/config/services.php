<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],
    'passport' => [
        'password_client_id' => env('PASSWORD_CLIENT_ID'),
        'password_client_secret' => env('PASSWORD_CLIENT_SECRET'),
    ],
    'categories' => [
        'base_uri' => env('CATEGORIES_SERVICE_BASE_URI')
    ],
    'boxes' => [
        'base_uri' => env('BOXES_SERVICE_BASE_URI')
    ],
    'options' => [
        'base_uri' => env('OPTIONS_SERVICE_BASE_URI')
    ],
    'suppliers' => [
        'base_uri' => env('SUPPLIERS_SERVICE_BASE_URI')
    ],
    'finder' => [
        'base_uri' => env('FINDER_SERVICE_BASE_URI')
    ]
    ,
    'assortments' => [
        'base_uri' => env('ASSORTMENTS_SERVICE_BASE_URI')
    ],
    'prices' => [
        'base_uri' => env('PRICES_SERVICE_BASE_URI')
    ],
    'margins' => [
        'base_uri' => env('MARGIN_SERVICE_BASE_URI')
    ],
    'discounts' => [
        'base_uri' => env('DISCOUNT_SERVICE_BASE_URI')
    ],
    'converter' => [
        'base_uri' => env('CONVERTER_SERVICE_BASE_URI')
    ],
    'fm' => [
        'base_uri' => env('FM_SERVICE_BASE_URI')
    ],
    'preflight' => [
        'base_uri' => env('PDF_SERVICE_BASE_URI')
    ],
    'pdf_co' => [
        'base_uri' => env('PDF_CO_URL')
    ],
    'pdf_tool' => [
        'base_uri' => env('PDF_TOOL_BASE_URI')
    ],
    'calculation' => [
        'base_uri' => env('CALCULATION_BASE_URI')
    ],
    'conneo' => [
        'base_uri' => env('CONNEO_BASE_URI'),
        'id' => env('CONNEO_ID'),
        'auth_token' => env('CONNEO_AUTH_TOKEN'),
    ],
    'machines' => [
        'base_uri' => env('MACHINES_SERVICE_BASE_URI')
    ],
    'catalogues' => [
        'base_uri' => env('CATALOGUES_SERVICE_BASE_URI')
    ],
    'geolocation' => [
        'base_uri' => env('GEOLOCATION_BASE_URI'),
        'access_token' => env('GEOLOCATION_ACCESS_TOKEN'),
    ],
    'supplier_categories' => [
        'base_uri' => env('SUPPLIER_CATEGORIES_SERVICE_BASE_URI'),
    ],
    'search' => [
        'base_uri' => env('FINDER_SEARCH_SERVICE_BASE_URI'),
    ],
    'marketplace' => [
        'base_uri' => env('MARKETPLACE_SERVICE_BASE_URI'),
    ],

];
