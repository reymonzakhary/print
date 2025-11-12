<?php

namespace App\Http\Controllers\Tenant\Mgr\Teams\Address;

use App\Http\Controllers\Controller;
use App\Http\Requests\Addresses\StoreAddressRequest;
use App\Http\Requests\Addresses\UpdateAddressRequest;
use App\Http\Resources\Address\AddressResource;
use App\Models\Tenant\Address;
use App\Models\Tenant\Team;
use App\Repositories\AddressRepository;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

/**
 * @group Tenant Teams
 *
 * @subgroup Tenant Team Addresses
 */
class AddressController extends Controller
{
    /**
     * default hiding field from response
     */
    private array $hide = [''];

    /**
     * @var AddressRepository
     */
    private AddressRepository $addressRepository;

    /**
     * AddressController constructor.
     * @param Address $address
     * @param team    $team
     */
    public function __construct(
        Address $address,
        Team    $team
    )
    {
        $this->addressRepository = new AddressRepository($address);
    }

    /**
     * Get Team Addresses
     *
     * return addresses of team
     *
     * @header Origin http://{sub_domin}.prindustry.test
     * @header Referer http://{sub_domin}.prindustry.test
     * @header Authorization Bearer token
     *
     * @urlParam team_id integer required The ID of the team.
     *
     * @response 200
     * {
     * 	"data":[
     *  {
     * 		"id": 2,
     * 		"address": "test",
     * 		"number": "5",
     * 		"city": "cairo",
     * 		"region": null,
     * 		"zip_code": "12345",
     * 		"default": false,
     * 		"type": "work",
     * 		"full_name": "test",
     * 		"company_name": "test",
     * 		"phone_number": "0123456789",
     * 		"tax_nr": "123456",
     * 		"lat": null,
     * 		"lng": null,
     * 		"created_at": "2024-05-08T14:01:43.000000Z",
     * 		"updated_at": "2024-05-08T14:01:43.000000Z"
     * 	},
     * ]
     * 	"status": 200,
     * 	"message": null
     * }
     *
     * @param team $team
     * @return mixed
     */
    public function index(
        team $team
    ): mixed
    {
        return AddressResource::collection(
            $team->addresses()->with('country')->get()
        )
            ->hide($this->hide)
            ->additional([
                'status' => Response::HTTP_OK,
                'message' => null
            ]);
    }

    /**
     * Store Team Address
     *
     * @header Origin http://{sub_domin}.prindustry.test
     * @header Referer http://{sub_domin}.prindustry.test
     * @header Authorization Bearer token
     *
     * @urlParam team integer required The ID of the team.
     *
     * @bodyParam address string required The address of team. Example: test street
     * @bodyParam number int required The number of address. Example: 1
     * @bodyParam city string required The city of address. Example: cairo
     * @bodyParam zip_code string required The zip code of address. Example: 11631
     * @bodyParam region string The region of address. Example: cairo
     * @bodyParam country_id bigint required The country id of address. Example: 1
     * @bodyParam type string The type of address. Example: invoice
     * @bodyParam company_name string The company name of address. Example: CHD
     * @bodyParam phone_number string The phone number of address. Example: 0123456789
     * @bodyParam tax_nr string The tax number of company. Example: 123456
     * @bodyParam full_name string The full name of address. Example: test
     * @bodyParam default boolean true if address is default . Example: true
     *
     * @response 201
     * {
     * 	"data": {
     * 		"id": 2,
     * 		"address": "test",
     * 		"number": "5",
     * 		"city": "cairo",
     * 		"region": null,
     * 		"zip_code": "12345",
     * 		"default": false,
     * 		"type": "work",
     * 		"full_name": "test",
     * 		"company_name": "test",
     * 		"phone_number": "0123456789",
     * 		"tax_nr": "123456",
     * 		"lat": null,
     * 		"lng": null,
     * 		"created_at": "2024-05-08T14:01:43.000000Z",
     * 		"updated_at": "2024-05-08T14:01:43.000000Z"
     * 	},
     * 	"status": 201,
     * 	"message": null
     * }
     *
     * @response 422
     * {
     *  "message": "The address field is required. (and 4 more errors)",
	 *  "errors": {
	 *  	"address": [
	 *  		"The address field is required."
	 *  	],
	 *  	"number": [
	 *  		"The number field is required."
	 *  	],
	 *  	"city": [
	 *  		"The city field is required."
	 *  	],
	 *  	"zip_code": [
	 *  		"The zip code field is required."
	 *  	],
	 *  	"country_id": [
	 *  		"The country id field is required."
	 *  	]
	 *  }
     * }
     *
     *
     * @param StoreAddressRequest $request
     * @param Team          $team
     * @return AddressResource
     */
    public function store(
        StoreAddressRequest $request,
        Team                 $team
    ): AddressResource
    {
        $address = $this->addressRepository->firstOrCreate($request->validated());

        $this->addressRepository->syncWithoutDetachingToModel($address, $team, $request->validated());

        return AddressResource::make(
            $team->addresses()
                ->where('addresses.id', $address->getAttribute('id'))
                ->with('country')
                ->first()
        )
            ->hide($this->hide)
            ->additional([
                'status' => Response::HTTP_CREATED,
                'message' => null
            ]);
    }

    /**
     * Update Team Address
     *
     * @header Origin http://{sub_domin}.prindustry.test
     * @header Referer http://{sub_domin}.prindustry.test
     * @header Authorization Bearer token
     *
     * @urlParam team integer required The ID of the team.
     * @urlParam address integer required The ID of the address.
     *
     * @bodyParam address string The address of team. Example: test street
     * @bodyParam number int required The number of address. Example: 1
     * @bodyParam city string The city of address. Example: cairo
     * @bodyParam zip_code string The zip code of address. Example: 11631
     * @bodyParam region string The region of address. Example: cairo
     * @bodyParam country_id bigint required The country id of address. Example: 1
     * @bodyParam state string The state of address. Example: test
     *
     * @response 201
     * {
     * 	"data": {
     * 		"id": 2,
     * 		"address": "test",
     * 		"number": "5",
     * 		"city": "cairo",
     * 		"region": null,
     * 		"zip_code": "12345",
     * 		"default": false,
     * 		"type": "work",
     * 		"full_name": "test",
     * 		"company_name": "test",
     * 		"phone_number": "0123456789",
     * 		"tax_nr": "123456",
     * 		"lat": null,
     * 		"lng": null,
     * 		"created_at": "2024-05-08T14:01:43.000000Z",
     * 		"updated_at": "2024-05-08T14:01:43.000000Z"
     * 	},
     * 	"status": 201,
     * 	"message": null
     * }
     *
     * @response 422
     * {
     *  "message": "The address field is required. (and 4 more errors)",
	 *  "errors": {
	 *  	"address": [
	 *  		"The address field is required."
	 *  	],
	 *  	"number": [
	 *  		"The number field is required."
	 *  	],
	 *  	"city": [
	 *  		"The city field is required."
	 *  	],
	 *  	"zip_code": [
	 *  		"The zip code field is required."
	 *  	],
	 *  	"country_id": [
	 *  		"The country id field is required."
	 *  	]
	 *  }
     * }
     *
     * @param team                 $team
     * @param Address              $address
     * @param UpdateAddressRequest $request
     * @return JsonResponse
     */
    public function update(
        Team                 $team,
        Address              $address,
        UpdateAddressRequest $request
    ): JsonResponse
    {
        if ($team->addresses()->detach($address->getAttribute('id'))) {
            $new = $this->addressRepository->firstOrCreate($request->validated());

            $this->addressRepository->syncWithoutDetachingToModel($new, $team, $request->validated());

            return response()->json([
                'message' => __("Address updated successfully,"),
                'status' => Response::HTTP_OK,
            ]);
        }

        /**
         * error response
         */
        return response()->json([
            'message' => __('We couldn\'t update the requested address.'),
            'status' => Response::HTTP_BAD_REQUEST
        ], Response::HTTP_BAD_REQUEST);
    }

    /**
     * @OA\Delete (
     *   tags={"teams"},
     *   path="users/{user_id}/addresses/{id}",
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
     * @param team    $team
     * @param Address $address
     * @return JsonResponse
     */
    public function destroy(
        team    $team,
        Address $address
    ): JsonResponse
    {
        if (
            $team->addresses()->detach($address->getAttribute('id'))
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
