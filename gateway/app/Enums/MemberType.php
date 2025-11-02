<?php

namespace App\Enums;

use EmreYarligan\EnumConcern\EnumConcern;

enum MemberType: string
{
    use EnumConcern;

    case INDIVIDUAL = 'individual';
    case BUSINESS = 'business';
}
