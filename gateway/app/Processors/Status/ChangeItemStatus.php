<?php

namespace App\Processors\Status;

use App\Enums\Status;
use App\Models\Tenants\Item;
use App\Models\Tenants\Order;

class ChangeItemStatus
{
    public function __invoke(Order $order, Item $item, $status)
    {
        $id = optional($item['product'])['item_ref'];
        if ($id){
            switchSupplier($order->connection);
            Item::where('id', $id)->update(['st' => $status]);
            switchSupplier($item->supplier_id);
        }
    }
}
