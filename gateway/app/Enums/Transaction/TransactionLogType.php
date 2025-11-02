<?php

declare(strict_types=1);

namespace App\Enums\Transaction;

use EmreYarligan\EnumConcern\EnumConcern;

enum TransactionLogType: string
{
    use EnumConcern;

    case REQUEST = 'request';
    case RESPONSE = 'response';
}
