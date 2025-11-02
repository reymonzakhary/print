<?php

namespace App\Http\Controllers\Tenant\Mgr\Orders\Discounts;

use App\Http\Controllers\Controller;
use App\Http\Requests\Order\DiscountOrderStoreRequest;
use App\Models\Tenants\Order;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class DiscountController extends Controller
{
    /**
     * @param Order                     $order
     * @param DiscountOrderStoreRequest $request
     * @return JsonResponse
     */
    public function __invoke(
        Order                     $order,
        DiscountOrderStoreRequest $request
    ): JsonResponse
    {
        if ($order->update($request->validated())) {
            return response()->json([
                'message' => __('Discount has been created successfully.'),
                'status' => Response::HTTP_CREATED
            ], Response::HTTP_CREATED);
        }

        return response()->json([
            'message' => __('We couldn\'t handle your request, please try again later.'),
            'status' => Response::HTTP_BAD_REQUEST
        ], Response::HTTP_BAD_REQUEST);
    }
}
