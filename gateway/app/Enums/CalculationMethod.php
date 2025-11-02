<?php

namespace App\Enums;

use EmreYarligan\EnumConcern\EnumConcern;

enum CalculationMethod: string
{
    use EnumConcern;

    case QTY = 'qty';
    case SQM = 'sqm';
    case SHEET = 'sheet';
    case LM = 'lm';
}
