<?php

declare(strict_types=1);

namespace App\Enums\Transaction;

use EmreYarligan\EnumConcern\EnumConcern;

enum TransactionType: string
{
    use EnumConcern;

    case PERIODIC = 'periodic';
    case SINGLE = 'single';
    case SPLIT = 'split';
    case CREDIT_NOTE = 'credit-note';
}
