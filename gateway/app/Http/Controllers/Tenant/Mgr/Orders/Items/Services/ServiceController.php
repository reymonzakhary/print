<?php

namespace App\Http\Controllers\Tenant\Mgr\Orders\Items\Services;

use App\Events\Tenant\Order\Item\Service\CreateOrderItemServiceEvent;
use App\Events\Tenant\Order\Item\Service\DeleteOrderItemServiceEvent;
use App\Events\Tenant\Order\Item\Service\UpdateOrderItemServiceEvent;
use App\Http\Controllers\Controller;
use App\Http\Requests\Order\ServiceOrderStoreRequest;
use App\Http\Requests\Order\ServiceOrderUpdateRequest;
use App\Http\Resources\Services\OrderServiceResource;
use App\Models\Tenants\Item;
use App\Models\Tenants\Order;
use App\Models\Tenants\Service;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class ServiceController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param Order $order
     * @param Item  $item
     * @return JsonResponse|Response
     */
    public function index(
        Order $order,
        Item  $item
    )
    {
        if ($order->items()->where('items.id', $item->id)->exists()) {
            return OrderServiceResource::collection($item->services()->get());
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
     * @param Order                    $order
     * @param Item                     $item
     * @return JsonResponse
     */
    public function store(
        ServiceOrderStoreRequest $request,
        Order                    $order,
        Item                     $item
    )
    {
        if ($order->items()->where('items.id', $item->id)->exists()) {
            collect($request->validated())->keyBy(function ($service) use ($item) {
                $item->services()->syncWithoutDetaching($service);
            });

            event(new CreateOrderItemServiceEvent($request->services, $order, $item, auth()->user()));

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
     * @param Order $order
     * @param Item  $item
     * @param int   $service
     * @return OrderServiceResource|JsonResponse
     */
    public function show(
        Order $order,
        Item  $item,
        int   $service
    )
    {
        if (
            $order->items()->where('items.id', $item->id)->exists() &&
            $item->services()->where('services.id', $service)->exists()
        ) {
            return OrderServiceResource::make(Service::where('id', $service)->first());
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
        Order                     $order,
        Item                      $item,
        int                       $id
    )
    {
        if (
            $order->items()->where('items.id', $item->id)->exists() &&
            $item->services()->where('services.id', $id)->exists()
        ) {
            $item->services()->updateExistingPivot($id, $request->validated());

            event(new UpdateOrderItemServiceEvent($order, $item, auth()->user()));
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
        Order $order,
        Item  $item,
        int   $id
    )
    {
        if (
            $order->items()->where('items.id', $item->id)->exists() &&
            $item->services()->where('services.id', $id)->exists()
        ) {
            $item->services()->detach($id);

            event(new DeleteOrderItemServiceEvent($id, $order, $item, auth()->user()));

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
