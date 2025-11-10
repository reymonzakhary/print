<?php

use App\Models\Tenant\DesignProviderTemplate;
use Illuminate\Http\Request;
use Spatie\SimpleExcel\SimpleExcelReader;

return [
    'mode' => [
        'DesignProviderTemplate' => DesignProviderTemplate::class,
        'SimpleExcelReader' => SimpleExcelReader::class,
        'request' => Request::class,
    ]
];
