<?php

namespace App\Enums;

use EmreYarligan\EnumConcern\EnumConcern;

enum CalculationType: string
{
    use EnumConcern;

    case KG = "kg";
    case SQM = "sqm";
    case SHEET = "sheet";
    case LM = "lm";
}
