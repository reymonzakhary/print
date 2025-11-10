<?php

namespace App\Http\Controllers\Tenant\Mgr\Company\Addresses;

use App\Http\Controllers\Controller;
use App\Http\Requests\Addresses\StoreAddressRequest;
use App\Http\Requests\Addresses\UpdateAddressRequest;
use App\Http\Resources\Address\AddressResource;
use App\Models\Tenant\Address;
use App\Models\Tenant\Company;
use App\Models\Tenant\User;
use App\Repositories\AddressRepository;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class AddressController extends Controller
{
    /**
     * default hiding field from response
     */
    private array $hide = [''];

    /**
     * @var AddressRepository
     */
    protected AddressRepository $address;

    /**
     * @var User
     */
    protected User $user;

    /**
     * AddressController constructor.
     * @param Address $address
     */
    public function __construct(
        Address $address
    )
    {
        $this->address = new AddressRepository($address);
    }

    /**
     * @OA\Get(
     *     tags={"Company"},
     *     path="/api/v1/mgr/company/addresses",
     *     summary="get company address list",
     *     security={{ "Bearer":{} }},
     *     @OA\SecurityScheme(
     *          securityScheme="bearerAuth",
     *          in="header",
     *          name="bearerAuth",
     *          type="oauth2",
     *          scheme="passport",
     *          bearerFormat="JWT",
     *     ),
     *     @OA\Response(response="200", description="success",@OA\JsonContent(ref="#/components/schemas/AddressResource")))
     * )
     *
     * @return AddressResource
     * @return JsonResponse
     */
    public function index()
    {
        $company = Company::main()->first();
        if (!$company) {
            return response()->json([
                "message" => __("Company not found!"),
                "status" => Response::HTTP_NOT_FOUND
            ], Response::HTTP_NOT_FOUND);
        }
        return AddressResource::collection(
            $company->addresses()->paginate(10)
        )
            ->hide($this->hide)
            ->additional([
                'status' => Response::HTTP_OK,
                'message' => null
            ]);
    }

    /**
     * @OA\Post(
     *   tags={"Company"},
     *   path="api/v1/mgr/companies/addresses",
     *   summary="create new company",
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
     *   @OA\Response(response="200", description="success",@OA\JsonContent(ref="#/components/schemas/AddressResource")))
     * )
     * @param StoreAddressRequest $request
     * @return AddressResource|JsonResponse
     */
    public function store(
        StoreAddressRequest $request,
    )
    {
        $company = Company::main()->first();
        if (!$company) {
            return response()->json([
                "message" => __("Company not found!"),
                "status" => Response::HTTP_NOT_FOUND
            ], Response::HTTP_NOT_FOUND);
        }
        $address = $this->address->firstOrCreate($request->validated());

        $addressable = collect($request->validated())->filter(function ($v, $k) {
            return in_array($k, ['type', 'full_name', 'company_name', 'phone_number', 'tax_nr'], true);
        })->toArray();

        $company
            ->addresses()
            ->syncWithoutDetaching([
                $address->id => $addressable
            ]);

        return AddressResource::make($company
            ->addresses()->where('addresses.id', $address->id)->first())->hide($this->hide)->additional([
            'status' => Response:: HTTP_CREATED,
            'message' => null
        ]);
        /**
         * error response
         */
        return response()->json([
            'data' => [
                'message' => __('addresses.company_not_found'),
                'status' => Response::HTTP_NOT_FOUND
            ]
        ], Response::HTTP_NOT_FOUND);
    }

    /**
     * @OA\PUT(
     *   tags={"Company"},
     *   path="api/v1/mgr/company/addresses/{address}",
     *   summary="update company address",
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
     *   @OA\Response(response="200", description="success",@OA\JsonContent(ref="#/components/schemas/AddressResource")))
     * )
     * @param Address              $address
     * @param UpdateAddressRequest $request
     * @return AddressResource|JsonResponse
     */
    public function update(
        Address              $address,
        UpdateAddressRequest $request
    )
    {
        $company = Company::main()->first();
        if (!$company) {
            return response()->json([
                "message" => __("Company not found!"),
                "status" => Response::HTTP_NOT_FOUND
            ], Response::HTTP_NOT_FOUND);
        }
        $search = collect($request->validated())->filter()->toArray();

        $results = $address->where(function ($q) use ($search) {
            return collect($search)->map(function ($keyword, $column) use ($q) {
                $q->where("{$column}", '=', "{$keyword}");
            });
        })->first();

        if ($results) {
            $company->addresses()->detach($address->id);
            $company
                ->addresses()
                ->syncWithoutDetaching([
                    $results->id => $request->only('type')
                ]);

            return AddressResource::make($results)
                ->hide($this->hide)
                ->additional([
                    'status' => Response::HTTP_OK,
                    'message' => null
                ]);
        }

        if ($company->addresses()->detach($address->id)) {
            $new = $this->address->firstOrCreate($search);
            $company
                ->addresses()
                ->syncWithoutDetaching([
                    $new->id => $request->only('type')
                ]);

            return AddressResource::make($new)->hide($this->hide)->additional([
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
     * @OA\Delete(
     *   tags={"Company"},
     *   path="api/v1/mgr/company/addresses/{address}",
     *   summary="delete company address",
     *   @OA\SecurityScheme(
     *         securityScheme="bearerAuth",
     *         in="header",
     *         name="bearerAuth",
     *         type="oauth2",
     *         scheme="passport",
     *         bearerFormat="JWT",
     *   ),
     *  @OA\Response(
     *    response=200,
     *    description="address removed succesfully",
     *    @OA\JsonContent(
     *       @OA\Property(property="message", type="string", example="address removed succesfully"),
     *      )
     *    ),
     *  )
     * )
     * @param Address $address
     * @return JsonResponse
     */
    public function destroy(
        Address $address
    )
    {
        $company = Company::main()->first();
        if (!$company) {
            return response()->json([
                "message" => __("Company not found!"),
                "status" => Response::HTTP_NOT_FOUND
            ], Response::HTTP_NOT_FOUND);
        }
        if ($company->addresses()->detach($address->id)) {
            /**
             * error response
             */
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
