<?php

namespace App\Enums;

use EmreYarligan\EnumConcern\EnumConcern;

enum ContractType: string
{
    use EnumConcern;

    case EXTERNAL = 'external';
    case INTERNAL = 'internal';
}
