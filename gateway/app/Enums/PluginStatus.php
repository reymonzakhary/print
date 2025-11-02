<?php

namespace App\Enums;

use EmreYarligan\EnumConcern\EnumConcern;

enum PluginStatus: string
{
    use EnumConcern;

    case PENDING = 'pending';
    case PROCESSING = 'processing';
    case COMPLETED = 'completed';
    case FAILED = 'failed';
    case FAILED_PERMANENTLY = 'failed_permanently';
}
