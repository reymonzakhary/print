<?php

namespace App\Http\Controllers\Tenant\Mgr\Warehouses\Addresses;

use App\Http\Controllers\Controller;
use App\Http\Requests\Addresses\StoreAddressRequest;
use App\Http\Requests\Addresses\UpdateAddressRequest;
use App\Http\Resources\Address\AddressResource;
use App\Models\Tenant\Address;
use App\Models\Tenant\Warehouse;
use App\Repositories\AddressRepository;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class AddressController extends Controller
{
    protected AddressRepository $address;

    public function __construct(
        Address $address,
    )
    {
        $this->address = new AddressRepository($address);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     *
     * @OA\Get(
     *   tags={"Warehouses"},
     *   path="api/v1/mgr/warehouses/{warehouse_id}/addresses",
     *   summary="list all addresses",
     *   @OA\SecurityScheme(
     *         securityScheme="bearerAuth",
     *         in="header",
     *         name="bearerAuth",
     *         type="oauth2",
     *         scheme="passport",
     *         bearerFormat="JWT",
     *   ),
     *   @OA\Response(response="200", description="success",@OA\JsonContent(ref="#/components/schemas/AddressResource"))),
     * )
     */
    public function index(Warehouse $warehouse)
    {
        return AddressResource::collection(
            $warehouse->address()->get()
        )
            ->additional([
                'status' => Response::HTTP_OK,
                'message' => null
            ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StoreAddressRequest $request
     * @param string|int          $id
     * @return AddressResource|JsonResponse
     *
     * @OA\Post (
     *   tags={"Warehouses"},
     *   path="api/v1/mgr/warehouses/{warehouse_id}/addresses",
     *   summary="list all addresses",
     *   @OA\SecurityScheme(
     *         securityScheme="bearerAuth",
     *         in="header",
     *         name="bearerAuth",
     *         type="oauth2",
     *         scheme="passport",
     *         bearerFormat="JWT",
     *   ),
     *   @OA\RequestBody(
     *      required=true,
     *      description="insert setting data",
     *      @OA\JsonContent(ref="#/components/schemas/StoreAddressRequest"),
     *   ),
     *   @OA\Response(response="200", description="success",@OA\JsonContent(ref="#/components/schemas/AddressResource"))),
     * )
     */
    public function store(
        StoreAddressRequest $request,
        string|int          $id
    )
    {
        $warehouse = Warehouse::find($id);

        if ($warehouse) {
            $address = $this->address->firstOrCreate($request->validated());

            $addressable = collect($request->validated())->filter(function ($v, $k) {
                return in_array($k, ['type', 'full_name', 'company_name', 'phone_number', 'tax_nr', 'default'], true);
            })->toArray();

            $warehouse
                ->address()
                ->syncWithoutDetaching([
                    $address->id => $addressable
                ]);

            return AddressResource::make(
                $warehouse->address()
                    ->where('addresses.id', $address->id)
                    ->first()
            )
                ->additional([
                    'status' => Response:: HTTP_CREATED,
                    'message' => null
                ]);
        }

        /**
         * error response
         */
        return response()->json([
            'message' => __('users.no_user_available'),
            'status' => Response::HTTP_NOT_FOUND
        ], 404);

    }

    /**
     * Update the specified resource in storage.
     *
     * @param Warehouse            $warehouse
     * @param Address              $address
     * @param UpdateAddressRequest $request
     * @return AddressResource|JsonResponse
     *
     * @OA\PUT (
     *   tags={"Warehouses"},
     *   path="api/v1/mgr/warehouses/{warehouse_id}/addresses/{id}",
     *   summary="list all addresses",
     *   @OA\SecurityScheme(
     *         securityScheme="bearerAuth",
     *         in="header",
     *         name="bearerAuth",
     *         type="oauth2",
     *         scheme="passport",
     *         bearerFormat="JWT",
     *   ),
     *   @OA\RequestBody(
     *      required=true,
     *      description="insert setting data",
     *      @OA\JsonContent(ref="#/components/schemas/UpdateAddressRequest"),
     *   ),
     *   @OA\Response(response="200", description="success",@OA\JsonContent(ref="#/components/schemas/AddressResource"))),
     * )
     */
    public function update(
        Warehouse            $warehouse,
        Address              $address,
        UpdateAddressRequest $request
    )
    {
        //@handel error response for not found exception
        $search = collect($request->validated())->filter()->toArray();

        $results = $address->where(function ($q) use ($search) {
            return collect($search)->map(function ($keyword, $column) use ($q) {
                $q->where("{$column}", '=', "{$keyword}");
            });
        })->first();

        if ($results) {
            $warehouse->address()->detach($address->id);
            $warehouse
                ->address()
                ->syncWithoutDetaching([
                    $results->id => $request->only(
                        'type', 'lng', 'lng', 'full_name', 'default',
                        'company_name', 'phone_number', 'tax_nr'
                    )
                ]);
            $default = collect($warehouse->address)->filter(function ($item) use ($results) {
                return $item->id === $results->id;
            })->first();

            return AddressResource::make($default)
                ->additional([
                    'status' => Response::HTTP_OK,
                    'message' => null
                ]);
        }

        if ($warehouse->address()->detach($address->id)) {
            $new = $this->address->firstOrCreate($search);
            $warehouse
                ->address()
                ->syncWithoutDetaching([
                    $new->id => $request->only('type')
                ]);

            return AddressResource::make($new)
                ->additional([
                    'status' => Response:: HTTP_OK,
                    'message' => null
                ]);
        }

        /**
         * error response
         */
        return response()->json([
            'data' => [
                'message' => __('addresses.bad_request')
            ]
        ], Response::HTTP_BAD_REQUEST);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Warehouse $warehouse
     * @param Address   $address
     * @return JsonResponse
     *
     * @OA\Delete (
     *   tags={"Warehouses"},
     *   path="api/v1/mgr/warehouses/{warehouse_id}/addresses/{id}",
     *   summary="Delete addresses",
     *   @OA\SecurityScheme(
     *         securityScheme="bearerAuth",
     *         in="header",
     *         name="bearerAuth",
     *         type="oauth2",
     *         scheme="passport",
     *         bearerFormat="JWT",
     *   ),
     *   @OA\Response(response="202", description="success",@OA\JsonContent(
     *          @OA\Property(property="message", type="string", example="addresses.address_removed"),
     *     ))),
     * )
     */
    public function destroy(
        Warehouse $warehouse,
        Address   $address
    )
    {
        if (
            $warehouse->address()->detach($address->id)
        ) {
            return response()->json([
                'data' => [
                    'message' => __('addresses.address_removed')
                ]
            ], Response::HTTP_ACCEPTED);
        }

        /**
         * error response
         */
        return response()->json([
            'data' => [
                'message' => __('addresses.bad_request')
            ]
        ], Response::HTTP_NOT_FOUND);
    }
}
