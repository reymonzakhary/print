<?php

namespace App\Enums;

use EmreYarligan\EnumConcern\EnumConcern;

enum MachineType: string
{
    use EnumConcern;

    case PRINTING = 'printing';
    case BUNDLING = 'bundling';
    case LAMINATION = 'lamination';
    case PUNCH_HOLES = 'punch_holes';
    case CUTTING = 'cutting';
    case COVERING = 'covering';
    case PAGES = 'pages';
}
