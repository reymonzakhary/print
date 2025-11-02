<?php

namespace App\Enums;

use EmreYarligan\EnumConcern\EnumConcern;

enum QueueProcessStatus: string
{
    use EnumConcern;

    case BACKGROUND = "background";

    case FOREGROUND = "foreground";
}
