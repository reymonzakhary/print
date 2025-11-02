<?php

declare(strict_types=1);

namespace App\Enums;

use EmreYarligan\EnumConcern\EnumConcern;

enum CategoryCalculationType: string
{
    use EnumConcern;

    case FULL_CALCULATION = 'full_calculation';
    case SEMI_CALCULATION = 'semi_calculation';
    case OPEN_CALCULATION = 'open_calculation';
    case EXTERNAL_CALCULATION = 'external_calculation';
}
