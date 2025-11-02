<?php

namespace App\Enums;

use EmreYarligan\EnumConcern\EnumConcern;

enum Units: string
{
    use EnumConcern;

    case MM = "mm";
    case CM = "cm";
    case INCH = "inch";
    case POINT = "point";
    case SQM = "sqm";

}
