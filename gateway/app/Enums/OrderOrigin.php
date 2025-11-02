<?php

declare(strict_types=1);

namespace App\Enums;

use EmreYarligan\EnumConcern\EnumConcern;

enum OrderOrigin: string
{
    use EnumConcern;

    case FromOrder = 'from-order';
    case FromShop = 'from-shop';
    case FromQuotation = 'from-quotation';
}
