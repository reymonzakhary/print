<?php

namespace App\Enums;

use EmreYarligan\EnumConcern\EnumConcern;

enum DesignProviderType: string
{
    use EnumConcern;

    case PRINDUSTRY = "prindustry";
    case CONNEO = "conneo";

}
