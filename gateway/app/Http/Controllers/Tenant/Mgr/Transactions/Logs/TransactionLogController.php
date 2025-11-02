<?php

declare(strict_types=1);

namespace App\Http\Controllers\Tenant\Mgr\Transactions\Logs;

use App\Http\Controllers\Controller;
use App\Http\Resources\Transactions\Log\TransactionLogResource;
use App\Models\Tenants\Transaction;
use Symfony\Component\HttpFoundation\Response;

final class TransactionLogController extends Controller
{

    /**
     * @param Transaction $transaction
     *
     * @return mixed
     */
    public function __invoke(
        Transaction $transaction
    ): mixed
    {
        return TransactionLogResource::collection(
            $transaction->logs()->get()
        )
            ->additional([
                'status' => Response::HTTP_OK,
                'message' => __('Transaction logs retrieved successfully.')
            ]);
    }

}
