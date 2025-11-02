<?php

namespace App\Enums;

use EmreYarligan\EnumConcern\EnumConcern;

enum AddressType: string
{
    use EnumConcern;

    case PICKUP = 'pickup';
    case INVOICE = 'invoice';
    case DELIVERY = 'delivery';
}
