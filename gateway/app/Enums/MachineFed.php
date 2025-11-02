<?php

namespace App\Enums;

use EmreYarligan\EnumConcern\EnumConcern;

enum MachineFed: string
{
    use EnumConcern;

    case SHEET = 'sheet';
    case ROLL = 'roll';
}
