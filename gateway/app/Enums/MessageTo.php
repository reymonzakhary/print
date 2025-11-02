<?php

namespace App\Enums;

use EmreYarligan\EnumConcern\EnumConcern;

enum MessageTo: string
{
    use EnumConcern;


    case SUPPLIER = "supplier";
    case CEC = "cec";

}
