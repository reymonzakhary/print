<?php

declare(strict_types=1);

namespace App\Http\Controllers\Tenant\Mgr\Orders\Transaction;

use App\Actions\PriceAction\CalculationAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Order\Transaction\UpdateTransactionRequest;
use App\Http\Resources\Orders\Transaction\TransactionResource;
use App\Http\Resources\Orders\Transaction\TransactionResourceCollection;
use App\Models\Tenants\Order;
use App\Models\Tenants\Transaction;
use App\Repositories\OrderRepository;
use App\Utilities\Order\Transaction\TransactionService;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Throwable;

final class TransactionController extends Controller
{
    /**
     * @param Order $order
     *
     * @return TransactionResourceCollection
     */
    public function index(
        Order $order
    ): TransactionResourceCollection
    {
        return TransactionResource::collection(
            $order->transactions()
                ->with([
                    'discount',
                    'team',
                    'parent'
                ])
                ->get()
        )->additional([
            'status' => Response::HTTP_OK,
            'message' => null,
        ]);
    }

    /**
     * @param Order $order
     * @param Transaction $transaction
     * @param OrderRepository $order_repository
     *
     * @return TransactionResource|JsonResponse
     */
    public function show(
        Order $order,
        Transaction $transaction,
        OrderRepository $order_repository
    ): TransactionResource|JsonResponse
    {
        if (!$order_repository->doesOrderOwnTheTransaction($order, $transaction)) {
            return response()->json([
                'status' => Response::HTTP_NOT_FOUND,
                'message' => __('Transaction is not related to the order.'),
            ]);
        }

        return TransactionResource::make(
            $transaction->load([
                'discount',
                'team',
                'parent'
            ])
        )->additional([
            'status' => Response::HTTP_OK,
            'message' => null,
        ]);
    }

    /**
     * Store a new transaction for the given order.
     *
     * @param Order $order The order to create the transaction for.
     *
     * @return TransactionResource A resource representing the created transaction.
     *
     * @throws HttpException
     */
    public function store(
        Order $order,
        TransactionService $transactionService,
    ): TransactionResource
    {
        return TransactionResource::make(
            $transactionService->performFreshCreationForTheTransaction((new CalculationAction($order))->calculate())
        )->additional([
            'message' => __('The transaction was successfully created.'),
            'status' => Response::HTTP_CREATED,
        ]);
    }

    /**
     * @param Order $order
     * @param Transaction $transaction
     * @param OrderRepository $orderRepository
     * @param UpdateTransactionRequest $request
     *
     * @return JsonResponse
     *
     * @throws Throwable
     */
    public function update(
        Order $order,
        Transaction $transaction,
        OrderRepository $orderRepository,
        UpdateTransactionRequest $request,
    ): JsonResponse
    {
        if (!$orderRepository->doesOrderOwnTheTransaction($order, $transaction)) {
            return response()->json([
                'status' => Response::HTTP_NOT_FOUND,
                'message' => __('Transaction is not related to the order.'),
            ]);
        }

        $transaction->updateOrFail($request->validated());

        return response()->json([
            'status' => Response::HTTP_OK,
            'message' => __('The transaction has been updated successfully.'),
        ]);
    }
}
