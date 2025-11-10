<?php

namespace App\Http\Controllers\Tenant\Mgr\Orders\Items\Discounts;

use App\Http\Controllers\Controller;
use App\Http\Requests\Items\DiscountItemsStoreRequest;
use App\Models\Tenant\Item;
use App\Models\Tenant\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;

class DiscountController extends Controller
{

    /**
     * @param Order                     $order
     * @param Item                      $item
     * @param DiscountItemsStoreRequest $request
     * @return JsonResponse
     */
    public function __invoke(
        Order                     $order,
        Item                      $item,
        DiscountItemsStoreRequest $request
    )
    {
        if (!auth()->user()->can('orders-items-discount-create')) {
            throw ValidationException::withMessages([
                'orders_service' => __('Not permitted action.')
            ]);
        }

        if (!$order->items()->where('items.id', $item->id)->exists()) {
            return response()->json([
                'message' => __('Item with id %s not found!'),
                'status' => Response::HTTP_NOT_FOUND
            ], Response::HTTP_NOT_FOUND);
        }

        if ($item->update($request->validated())) {
            return response()->json([
                'message' => __('Discount has been updated successfully.'),
                'status' => Response::HTTP_CREATED
            ], Response::HTTP_CREATED);
        }

        return response()->json([
            'message' => __('We couldn\'t handle your request, please try again later.'),
            'status' => Response::HTTP_BAD_REQUEST
        ], Response::HTTP_BAD_REQUEST);

    }
}
