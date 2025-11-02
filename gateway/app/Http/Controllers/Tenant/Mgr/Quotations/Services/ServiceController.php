<?php

declare(strict_types=1);

namespace App\Http\Controllers\Tenant\Mgr\Quotations\Services;

use App\Events\Tenant\Order\Service\CreateOrderServiceEvent;
use App\Events\Tenant\Order\Service\DeleteOrderServiceEvent;
use App\Events\Tenant\Order\Service\UpdateOrderServiceEvent;
use App\Http\Controllers\Controller;
use App\Http\Requests\Order\ServiceOrderStoreRequest;
use App\Http\Requests\Order\ServiceOrderUpdateRequest;
use App\Http\Resources\Services\QuotationServiceResource;
use App\Http\Resources\Services\QuotationServiceResourceCollection;
use App\Models\Tenants\Quotation;
use App\Models\Tenants\Service;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

final class ServiceController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param Quotation $quotation
     *
     * @return mixed
     */
    public function index(
        Quotation $quotation
    ): mixed
    {
        return QuotationServiceResource::collection(
            $quotation->services()->get()
        );
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param ServiceOrderStoreRequest $request
     * @param Quotation                $quotation
     *
     * @return JsonResponse
     */
    public function store(
        ServiceOrderStoreRequest $request,
        Quotation $quotation
    ): JsonResponse
    {
        if (!$createdService = $quotation->services()->create($request->validated())) {
            return response()->json([
                'status' => Response::HTTP_UNPROCESSABLE_ENTITY,
                'message' => __('Could not create the service')
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        if (!$quotation->services()->syncWithoutDetaching(
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

        event(new CreateOrderServiceEvent($createdService->getAttribute('id'), $quotation, auth()->user()));

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
     * @param Quotation $quotation
     * @param int       $id
     *
     * @return QuotationServiceResource|JsonResponse
     */
    public function show(
        Quotation $quotation,
        int       $id
    ): JsonResponse|QuotationServiceResource
    {
        if ($quotation->services()->where('services.id', $id)->exists()) {
            return QuotationServiceResource::make(Service::where('id', $id)->first());
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
     * @param Quotation                 $quotation
     * @param int                       $id
     *
     * @return JsonResponse
     */
    public function update(
        ServiceOrderUpdateRequest $request,
        Quotation                 $quotation,
        int                       $id
    ): JsonResponse
    {
        if ($service = $quotation->services()->where('services.id', $id)->first()) {
            $service->updateOrFail($request->validated());

            $quotation->services()->updateExistingPivot(
                $service->getAttribute('id'),
                $request->safe([
                    'vat',
                    'qty'
                ])
            );

            event(new UpdateOrderServiceEvent($service, $quotation, auth()->user()));

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
     * @param Quotation $quotation
     * @param int       $id
     *
     * @return JsonResponse
     */
    public function destroy(
        Quotation $quotation,
        int       $id
    ): JsonResponse
    {
        if ($quotation->services()->where('services.id', $id)->exists()) {

            $quotation->services()->detach($id);

            event(new DeleteOrderServiceEvent($id, $quotation, auth()->user()));

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
