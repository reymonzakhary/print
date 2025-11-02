<?php

declare(strict_types=1);

namespace App\Http\Controllers\Tenant\Mgr\Orders\Services;

use App\Events\Tenant\Order\Service\CreateOrderServiceEvent;
use App\Events\Tenant\Order\Service\DeleteOrderServiceEvent;
use App\Events\Tenant\Order\Service\UpdateOrderServiceEvent;
use App\Http\Controllers\Controller;
use App\Http\Requests\Order\ServiceOrderStoreRequest;
use App\Http\Requests\Order\ServiceOrderUpdateRequest;
use App\Http\Resources\Services\OrderServiceResource;
use App\Models\Tenants\Order;
use App\Models\Tenants\Service;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

final class ServiceController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param Order $order
     *
     * @return mixed
     */
    public function index(
        Order $order
    ): mixed
    {
        return OrderServiceResource::collection(
            $order->services()->get()
        );
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param ServiceOrderStoreRequest $request
     * @param Order $order
     *
     * @return JsonResponse
     */
    public function store(
        ServiceOrderStoreRequest $request,
        Order                    $order
    ): JsonResponse
    {
        if (!$createdService = $order->services()->create($request->validated())) {
            return response()->json([
                'status' => Response::HTTP_UNPROCESSABLE_ENTITY,
                'message' => __('Could not create the service')
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        if (!$order->services()->syncWithoutDetaching(
            [
                $createdService->getAttribute('id') => [
                    'qty' => $request->validated('qty'),
                    'vat' => $request->validated('vat'),
                ]
            ]
        )) {
            return response()->json([
                'status' => Response::HTTP_UNPROCESSABLE_ENTITY,
                'message' => __('Failed to update the intermediate table')
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        event(new CreateOrderServiceEvent($createdService->getAttribute('id'), $order, auth()->user()));

        /**
         *  response
         */
        return response()->json([
            'message' => __('services.added'),
            'status' => Response::HTTP_CREATED
        ], Response::HTTP_CREATED);
    }

    /**
     * Display the specified resource.
     *
     * @param Order $order
     * @param int $id
     *
     * @return JsonResponse|OrderServiceResource
     */
    public function show(
        Order $order,
        int   $id
    ): JsonResponse|OrderServiceResource
    {
        if ($order->services()->where('services.id', $id)->exists()) {
            return OrderServiceResource::make(Service::where('id', $id)->first());
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
     * @param Order $order
     * @param int $id
     *
     * @return JsonResponse
     */
    public function update(
        ServiceOrderUpdateRequest $request,
        Order                     $order,
        int                       $id
    ): JsonResponse
    {
        if ($service = $order->services()->where('services.id', $id)->first()) {
            $service->updateOrFail($request->validated());

            $order->services()->updateExistingPivot(
                $service->getAttribute('id'),
                $request->safe([
                    'vat',
                    'qty'
                ])
            );

            event(new UpdateOrderServiceEvent($service, $order, auth()->user()));

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
     * @param int $id
     *
     * @return JsonResponse
     */
    public function destroy(
        Order $order,
        int   $id
    ): JsonResponse
    {
        if ($order->services()->where('services.id', $id)->exists()) {

            $order->services()->detach($id);

            event(new DeleteOrderServiceEvent($id, $order, auth()->user()));

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

