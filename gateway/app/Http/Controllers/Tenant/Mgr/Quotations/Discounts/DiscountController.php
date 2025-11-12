<?php

namespace App\Http\Controllers\Tenant\Mgr\Quotations\Discounts;

use App\Http\Controllers\Controller;
use App\Http\Requests\Quotation\DiscountQuotationStoreRequest;
use App\Models\Tenant\Quotation;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class DiscountController extends Controller
{
    /**
     * @param Quotation                     $quotation
     * @param DiscountQuotationStoreRequest $request
     * @return JsonResponse
     */
    public function __invoke(
        Quotation                     $quotation,
        DiscountQuotationStoreRequest $request
    ): JsonResponse
    {
        if ($quotation->update($request->validated())) {
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
