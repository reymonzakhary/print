<?php

declare(strict_types=1);

namespace App\Utilities\Order\Transaction;

use App\DTO\Tenant\Orders\Transaction\TransactionDTO;
use App\Enums\Status;
use App\Models\Tenant\Order;
use App\Models\Tenant\Transaction;
use LogicException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

final readonly class TransactionService
{
    /**
     * If the order status is not final yet, then delete all old transactions and create a fresh new one
     *
     * @throws HttpException
     */
    public function performFreshCreationForTheTransaction(Order $calculatedOrder): Transaction
    {
        if ($this->isOrderStatusFinal($calculatedOrder)) {
            throw new HttpException(
                statusCode: Response::HTTP_UNPROCESSABLE_ENTITY,

                message: __(
                    'It is not possible to do a manual creation of the transaction as the order has reached a final status.'
                )
            );
        }

        $calculatedOrder->transactions()->forceDelete();
        return $calculatedOrder->transactions()->create(TransactionDTO::toTransaction($calculatedOrder));
    }

    /**
     * Should create a new transaction if the order is in the applicable state
     *
     * @param Order $calculatedOrder
     *
     * @return void
     */
    public function createTransactionIfOrderInApplicableStateForAutoTransactionCreation(Order $calculatedOrder): void
    {
        $this->validateOrder($calculatedOrder);

        if ($calculatedOrder->transactions()->exists() || !$this->isOrderStatusFinal($calculatedOrder)) {
            return;
        }

        $calculatedOrder->transactions()->create(TransactionDTO::toTransaction($calculatedOrder));
    }

    /**
     * Provision transactions of an order
     *
     * @param Order $order
     *
     * @return void
     *
     */
    public function provisionTransactionsBasedOnOrder(Order $order): void
    {
        if ($order->getAttribute('st') === Status::DRAFT->value) {
            return;
        }

        $draftTransactions = $order->transactions()->where('st', Status::DRAFT->value);

        if (0 === $draftTransactions->count()) {
            return;
        }

        $draftTransactions->update(['st' => Status::UN_PAID->value]);
    }

    /**
     * Check if the order status can be considered as final
     *
     * @param Order $order
     *
     * @return bool
     */
    private function isOrderStatusFinal(Order $order): bool
    {
        return in_array(
            $order->getAttribute('st'),
            $this->getFinalStatusesForTheOrder(),
            true
        );
    }

    /**
     * Get the order's statuses that can be considered as final
     *
     * A final status is status in which the order will not have any major updated
     * that might affect the accuracy of a generated transaction (e.g. adding or removing items, changing prices, etc.)
     *
     * @return array
     */
    private function getFinalStatusesForTheOrder(): array
    {
        return [
            Status::IN_PRODUCTION->value,
            Status::READY->value,
            Status::BEING_SHIPPED->value,
            Status::DELIVERED->value
        ];
    }

    /**
     * Validate that a given object is meeting order criteria
     *
     * @param Order $order
     *
     * @return void
     */
    private function validateOrder(Order $order): void
    {
        if (true !== $order->getAttribute('type')) {
            throw new LogicException('Order type is not valid');
        }
    }
}
