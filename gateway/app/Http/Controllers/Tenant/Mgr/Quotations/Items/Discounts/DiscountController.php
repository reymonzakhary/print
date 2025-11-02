<?php

namespace App\Http\Controllers\Tenant\Mgr\Quotations\Items\Discounts;

use App\Http\Controllers\Controller;
use App\Http\Requests\Items\DiscountItemsStoreRequest;
use App\Models\Tenants\Item;
use App\Models\Tenants\Quotation;
use Symfony\Component\HttpFoundation\Response;

class DiscountController extends Controller
{
    public function __invoke(
        Quotation                 $quotation,
        Item                      $item,
        DiscountItemsStoreRequest $request
    )
    {
        if (!$quotation->items()->where('items.id', $item->id)->exists()) {
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
