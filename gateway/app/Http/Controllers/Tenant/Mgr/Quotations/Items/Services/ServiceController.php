<?php

namespace App\Http\Controllers\Tenant\Mgr\Quotations\Items\Services;

use App\Events\Tenant\Order\Item\Service\CreateOrderItemServiceEvent;
use App\Events\Tenant\Order\Item\Service\DeleteOrderItemServiceEvent;
use App\Events\Tenant\Order\Item\Service\UpdateOrderItemServiceEvent;
use App\Http\Controllers\Controller;
use App\Http\Requests\Order\ServiceOrderStoreRequest;
use App\Http\Requests\Order\ServiceOrderUpdateRequest;
use App\Http\Resources\Services\QuotationServiceResource;
use App\Http\Resources\Services\ServiceResource;
use App\Models\Tenant\Item;
use App\Models\Tenant\Order;
use App\Models\Tenant\Quotation;
use App\Models\Tenant\Service;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class ServiceController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param Quotation $quotation
     * @param Item $item
     * @return JsonResponse|Response
     */
    public function index(
        Quotation $quotation,
        Item      $item
    ): Response|JsonResponse
    {
        if ($quotation->items()->where('items.id', $item->id)->exists()) {
            return QuotationServiceResource::collection($item->services()->get());
        }

        /**
         * error response
         */
        return response()->json([
            'message' => __('services.no_service_available'),
            'status' => Response::HTTP_NOT_FOUND
        ], 404);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param ServiceOrderStoreRequest $request
     * @param Quotation                $quotation
     * @param Item                     $item
     * @return JsonResponse
     */
    public function store(
        ServiceOrderStoreRequest $request,
        Quotation                $quotation,
        Item                     $item
    )
    {
        if ($quotation->items()->where('items.id', $item->id)->exists()) {
            collect($request->validated())->keyBy(function ($service) use ($item) {
                $item->services()->syncWithoutDetaching($service);
            });

            event(new CreateOrderItemServiceEvent($request->services, $quotation, $item, auth()->user()));

            /**
             *  response
             */
            return response()->json([
                'message' => __('services.added'),
                'status' => Response::HTTP_CREATED
            ], Response::HTTP_CREATED);
        }

        /**
         * error response
         */
        return response()->json([
            'message' => __('services.not_found'),
            'status' => Response::HTTP_NOT_FOUND
        ], 404);
    }

    /**
     * Display the specified resource.
     *
     * @param Quotation $quotation
     * @param Item  $item
     * @param int   $service
     * @return QuotationServiceResource|JsonResponse
     */
    public function show(
        Quotation $quotation,
        Item      $item,
        int       $service
    )
    {
        if (
            $quotation->items()->where('items.id', $item->id)->exists() &&
            $item->services()->where('services.id', $service)->exists()
        ) {
            return QuotationServiceResource::make(Service::where('id', $service)->first());
        }

        /**
         * error response
         */
        return response()->json([
            'message' => __('services.not_found'),
            'status' => Response::HTTP_NOT_FOUND
        ], 404);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param ServiceOrderUpdateRequest $request
     * @param Order                     $order
     * @param Item                      $item
     * @param int                       $id
     * @return JsonResponse
     */
    public function update(
        ServiceOrderUpdateRequest $request,
        Quotation                 $quotation,
        Item                      $item,
        int                       $id
    )
    {
        if (
            $quotation->items()->where('items.id', $item->id)->exists() &&
            $item->services()->where('services.id', $id)->exists()
        ) {
            $item->services()->updateExistingPivot($id, $request->validated());

            event(new UpdateOrderItemServiceEvent($quotation, $item, auth()->user()));
            /**
             *  response
             */
            return response()->json([
                'message' => __('services.updated'),
                'status' => Response::HTTP_OK
            ], Response::HTTP_OK);
        }

        /**
         * error response
         */
        return response()->json([
            'message' => __('services.not_found'),
            'status' => Response::HTTP_NOT_FOUND
        ], 404);

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Order $order
     * @param Item  $item
     * @param int   $id
     * @return JsonResponse|void
     */
    public function destroy(
        Quotation $quotation,
        Item      $item,
        int       $id
    )
    {
        if (
            $quotation->items()->where('items.id', $item->id)->exists() &&
            $item->services()->where('services.id', $id)->exists()
        ) {
            $item->services()->detach($id);

            event(new DeleteOrderItemServiceEvent($id, $quotation, $item, auth()->user()));

            return response()->json([
                'message' => __('services.service_removed'),
                'status' => Response::HTTP_OK
            ], Response::HTTP_OK);
        }

        /**
         * error response
         */
        return response()->json([
            'message' => __('services.bad_request'),
            'status' => Response::HTTP_BAD_REQUEST
        ], Response::HTTP_BAD_REQUEST);
    }
}
