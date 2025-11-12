<?php

namespace App\Contracts;


use App\Models\Tenant\Item;
use App\Models\Tenant\Order;
use Exception;

abstract class JobTicketAbstract
{
    public function format(Order $order, Item $item, string $iso, string $tenant)
    {
        throw new Exception('We need implement Format Function');
    }
}
