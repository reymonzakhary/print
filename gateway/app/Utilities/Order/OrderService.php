<?php

declare(strict_types=1);

namespace App\Utilities\Order;

use App\Enums\Status;
use App\Models\Tenants\Item;
use App\Models\Tenants\Order;
use App\Repositories\OrderRepository;
use Throwable;

final readonly class OrderService
{
    public function __construct(
        private OrderRepository $orderRepository
    ) {
    }

    /**
     * @param Order $order
     *
     * @return void
     *
     * @throws Throwable
     */
    public function makeOrderBasedOnItems(Order $order): void
    {
        if ($order->items()->count() === 0) {
            return;
        }

        match (true) {
            $order->items()->get(['st'])->every('st', '===', Status::CANCELED->value)
                => $order->setAttribute('st', Status::CANCELED->value),

            $order->items()->whereStatusIsNotCancelled()->get(['st'])->every('st', '===', Status::DELIVERED->value)
                => $order->setAttribute('st', Status::DONE->value),

            ($itemWithLowestStatus = $this->orderRepository->getItemWithLowestSequenceStatus($order)) && $itemWithLowestStatus instanceof Item
                => $order->setAttribute('st', $itemWithLowestStatus->getAttribute('st'))
        };

        $order->saveOrFail();
    }
}
