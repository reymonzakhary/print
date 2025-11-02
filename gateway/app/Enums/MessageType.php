<?php

namespace App\Enums;

use EmreYarligan\EnumConcern\EnumConcern;

enum MessageType: string
{
    use EnumConcern;

    case PRODUCER = 'producer';
    case CONTRACT = 'contract';

}
