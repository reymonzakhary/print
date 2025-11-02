<?php

namespace App\Http\Controllers\Tenant\Mgr\Services;

use App\Http\Controllers\Controller;
use App\Http\Requests\Services\StoreServiceRequest;
use App\Http\Requests\Services\UpdateServiceRequest;
use App\Http\Resources\Services\ServiceResource;
use App\Models\Tenants\Service;
use App\Repositories\ServiceRepository;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\ValidationException;

/**
 * @group Tenant Services
 */
class ServiceController extends Controller
{
    /**
     * @var ServiceRepository
     */
    protected ServiceRepository $service;

    /**
     * default total result in one page
     */
    protected int $per_page = 10;

    /**
     * UserController constructor.
     * @param Request $request
     * @param Service $service
     */
    public function __construct(
        Request $request,
        Service $service
    )
    {
        $this->service = new ServiceRepository($service);

        /**
         * default number of pages
         */
        $this->per_page = $request->get('per_page') ?? $this->per_page;
    }

    /**
     * List all services
     *
     * @header Origin http://{sub_domin}.prindustry.test
     * @header Referer http://{sub_domin}.prindustry.test
     * @header Authorization Bearer token
     *
     * @response 200
     * {
	 * "data": [
	 * 	{
	 * 		"id": 1,
	 * 		"name": "software",
	 * 		"slug": "software",
	 * 		"description": null,
	 * 		"display_price": "€ 10,00",
	 * 		"price": 1000,
	 * 		"vat_id": null,
	 * 		"created_at": "2024-05-16T14:16:24.000000Z",
	 * 		"updated_at": "2024-05-16T14:16:24.000000Z"
	 * 	}
	 * ],
     * "links":{},
     * "meta":{},
     * "status":200,
     * "message": null
     * }
     *
     * @response 404
     * {
     * "message":"Not Found",
     * "status":404
     * }
     *
     * @return mixed
     */
    public function index()
    {
        if (!auth()->user()->can('orders-services-list')) {
            throw ValidationException::withMessages([
                'orders_services' => __('Not permitted action.')
            ]);
        }

        /** @var service obtain  $service */
        $service = $this->service->all($this->per_page);

        /**
         * check if we have service
         */
        if ($service->items()) {
            return ServiceResource::collection($service)->additional([
                'status' => Response::HTTP_OK,
                'message' => null
            ]);
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
     * Show Service
     *
     * @header Origin http://{sub_domin}.prindustry.test
     * @header Referer http://{sub_domin}.prindustry.test
     * @header Authorization Bearer token
     *
     * @response 200
     * {
     * 	"data": {
     * 		"id": 1,
     * 		"name": "software",
     * 		"slug": "software",
     * 		"description": null,
     * 		"display_price": "€ 10,00",
     * 		"price": 1000,
     * 		"vat_id": null,
     * 		"created_at": "2024-05-16T14:16:24.000000Z",
     * 		"updated_at": "2024-05-16T14:16:24.000000Z"
     * 	},
     * 	"status": 200,
     * 	"message": null
     * }
     *
     * @response 404
     * {
     * "message":"Not Found",
     * "status":404
     * }
     *
     * @return mixed
     */
    public function show($service_id)
    {
        if (!auth()->user()->can('orders-services-access')) {
            throw ValidationException::withMessages([
                'orders_services' => __('Not permitted action.')
            ]);
        }

        /** @var service obtain  $service */
        $service = $this->service->show($service_id);
        /**
         * check if we have service
         */
        if ($service) {
            return ServiceResource::make($service)->additional([
                'status' => Response::HTTP_OK,
                'message' => null
            ]);
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
     * Store Service
     *
     * @header Origin http://{sub_domin}.prindustry.test
     * @header Referer http://{sub_domin}.prindustry.test
     * @header Authorization Bearer token
     *
     * @response 201
     * {
     * 	"data": {
     * 		"id": 1,
     * 		"name": "software",
     * 		"slug": "software",
     * 		"description": null,
     * 		"display_price": "€ 10,00",
     * 		"price": 1000,
     * 		"vat_id": null,
     * 		"created_at": "2024-05-16T14:16:24.000000Z",
     * 		"updated_at": "2024-05-16T14:16:24.000000Z"
     * 	},
     * 	"status": 201,
     * 	"message": null
     * }
     *
     * @response 422
     * {
     * 	"message": "The name field is required. (and 1 more error)",
     * 	"errors": {
     * 		"name": [
     * 			"The name field is required."
     * 		],
     * 		"price": [
     * 			"The price field is required."
     * 		]
     * 	}
     * }
     *
     * @param StoreServiceRequest $request
     * @return ServiceResource
     */
    public function store(
        StoreServiceRequest $request
    )
    {
        if (!auth()->user()->can('orders-services-create')) {
            throw ValidationException::withMessages([
                'orders_services' => __('Not permitted action.')
            ]);
        }

        return ServiceResource::make(
            $this->service->create(
                $request->validated()
            )
        )
            ->additional([
                'status' => Response::HTTP_CREATED,
                'message' => null
            ]);
    }

    /**
     * Update service
     *
     * @header Origin http://{sub_domin}.prindustry.test
     * @header Referer http://{sub_domin}.prindustry.test
     * @header Authorization Bearer token
     *
     * @param UpdateServiceRequest $request
     * @param int                  $id
     * @return ServiceResource|JsonResponse
     */
    public function update(
        UpdateServiceRequest $request,
        int                  $id
    )
    {
        if (!auth()->user()->can('orders-services-update')) {
            throw ValidationException::withMessages([
                'orders_services' => __('Not permitted action.')
            ]);
        }

        if (
            $this->service->update(
                $id,
                $request->validated()
            )
        ) {
            return ServiceResource::make($this->service->show($id))
                ->additional([
                    'status' => Response::HTTP_OK,
                    'message' => __('services.updated'),
                ]);
        }

        /**
         * error response
         */
        return response()->json([
            'message' => __('services.not_found'),
            'status' => Response::HTTP_BAD_REQUEST

        ], Response::HTTP_BAD_REQUEST);
    }

    /**
     * Delete Service
     *
     * @header Origin http://{sub_domin}.prindustry.test
     * @header Referer http://{sub_domin}.prindustry.test
     * @header Authorization Bearer token
     *
     * @param int $id
     * @return JsonResponse
     */
    public function destroy(
        int $id
    )
    {
        if (!auth()->user()->can('orders-services-deleted')) {
            throw ValidationException::withMessages([
                'orders_services' => __('Not permitted action.')
            ]);
        }

        if (
            $this->service->delete(
                $id
            )
        ) {
            /**
             * error response
             */
            return response()->json([
                'data' => [
                    'message' => __('services.service_removed'),
                ]
            ], Response::HTTP_ACCEPTED);
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
