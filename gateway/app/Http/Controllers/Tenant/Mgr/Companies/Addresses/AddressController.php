<?php

namespace App\Http\Controllers\Tenant\Mgr\Companies\Addresses;

use App\Http\Controllers\Controller;
use App\Http\Requests\Addresses\StoreAddressRequest;
use App\Http\Requests\Addresses\UpdateAddressRequest;
use App\Http\Resources\Address\AddressResource;
use App\Models\Tenants\Address;
use App\Models\Tenants\Company;
use App\Repositories\AddressRepository;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

/**
 * @group Tenant Companies
 *
 * @subgroup Tenant Company Addresses
 * @subgroupDescription
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
    protected AddressRepository $address;

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
     * List Company addresses
     *
     * @header Origin http://{sub_domin}.prindustry.test
     * @header Referer http://{sub_domin}.prindustry.test
     * @header Authorization Bearer token
     *
     * @response 200
     * {
     * "data": [
	 * 	{
	 * 		"id": 7,
	 * 		"address": "el Nozha",
	 * 		"number": "5         ",
	 * 		"city": "cairo",
	 * 		"region": null,
	 * 		"zip_code": "132456",
	 * 		"default": false,
	 * 		"type": null,
	 * 		"full_name": null,
	 * 		"company_name": null,
	 * 		"phone_number": null,
	 * 		"tax_nr": null,
	 * 		"lat": null,
	 * 		"lng": null,
	 * 		"created_at": "2024-05-16T12:14:20.000000Z",
	 * 		"updated_at": "2024-05-16T12:14:20.000000Z"
	 * 	}
	 * ],
     * "links":{},
     * "meta":{},
     * "status":200,
     * "message": null
     * }
     *
     * @param Company $company
     * @return AddressResource
     */
    public function index(
        Company $company
    )
    {
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
     * store Company address
     *
     * @header Origin http://{sub_domin}.prindustry.test
     * @header Referer http://{sub_domin}.prindustry.test
     * @header Authorization Bearer token
     *
     * @response 201
     * {
     * 	"data": {
     * 		"id": 7,
     * 		"address": "el Nozha",
     * 		"number": "5         ",
     * 		"city": "cairo",
     * 		"region": null,
     * 		"zip_code": "132456",
     * 		"default": false,
     * 		"type": null,
     * 		"full_name": null,
     * 		"company_name": null,
     * 		"phone_number": null,
     * 		"tax_nr": null,
     * 		"lat": null,
     * 		"lng": null,
     * 		"created_at": "2024-05-16T12:14:20.000000Z",
     * 		"updated_at": "2024-05-16T12:14:20.000000Z"
     * 	},
     * 	"status": 201,
     * 	"message": null
     * }
     *
     * @response 422
     * {
     * 	"message": "The address field is required. (and 4 more errors)",
     * 	"errors": {
     * 		"address": [
     * 			"The address field is required."
     * 		],
     * 		"number": [
     * 			"The number field is required."
     * 		],
     * 		"city": [
     * 			"The city field is required."
     * 		],
     * 		"zip_code": [
     * 			"The zip code field is required."
     * 		],
     * 		"country_id": [
     * 			"The country id field is required."
     * 		]
     * 	}
     * }
     *
     * @param StoreAddressRequest $request
     * @param Company             $company
     * @return AddressResource
     */
    public function store(
        StoreAddressRequest $request,
        Company             $company
    )
    {
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
    }

    /**
     * update company address
     *
     * @header Origin http://{sub_domin}.prindustry.test
     * @header Referer http://{sub_domin}.prindustry.test
     * @header Authorization Bearer token
     *
     *
     * @param Company              $company
     * @param Address              $address
     * @param UpdateAddressRequest $request
     * @return AddressResource|JsonResponse
     */
    public function update(
        Company              $company,
        Address              $address,
        UpdateAddressRequest $request
    )
    {
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
     * delete company address
     * @header Origin http://{sub_domin}.prindustry.test
     * @header Referer http://{sub_domin}.prindustry.test
     * @header Authorization Bearer token
     *
     * @param Company $company
     * @param Address $address
     * @return JsonResponse
     */
    public function destroy(
        Company $company,
        Address $address
    )
    {
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
