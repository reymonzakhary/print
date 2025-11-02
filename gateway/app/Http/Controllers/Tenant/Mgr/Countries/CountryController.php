<?php

namespace App\Http\Controllers\Tenant\Mgr\Countries;

use App\Http\Controllers\Controller;
use App\Http\Requests\Addresses\StoreAddressRequest;
use App\Http\Resources\Address\AddressResource;
use App\Http\Resources\Country\CountryResource;
use App\Models\Tenants\Address;
use App\Models\Tenants\Country;
use App\Repositories\CountryRepository;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

/**
 * @group Tenant Countries
 */
class CountryController extends Controller
{
    /**
     * @var CountryRepository
     */
    protected CountryRepository $country;

    /**
     * @var array
     */
    public array $hide = ['number'];

    /**
     * AddressController constructor.
     * @param Country $country
     */
    public function __construct(
        Country $country
    )
    {
        $this->country = new CountryRepository($country);
    }

    /**
     * List Countries 
     * 
     * @header Origin http://{sub_domin}.prindustry.test
     * @header Referer http://{sub_domin}.prindustry.test
     * @header Authorization Bearer token
     * 
     * @response 200 
     * {
     * "data": [
	 *	{
	 *		"id": 1,
	 *		"name": "AFGHANISTAN",
	 *		"iso2": "AF",
	 *		"iso3": "AFG",
	 *		"un_code": 4,
	 *		"dial_code": "93",
	 *		"created_at": null,
	 *		"updated_at": null
	 *	},
     *  ],
     * "status": 200,
	 * "message": null
     * }
     * @return mixed
     */
    public function index()
    {
        return CountryResource::collection($this->country->all())
            ->hide($this->hide)
            ->additional([
                'status' => Response::HTTP_OK,
                'message' => null
            ]);
    }

    /**
     * Search of Country Addresses 
     * 
     * @header Origin http://{sub_domin}.prindustry.test
     * @header Referer http://{sub_domin}.prindustry.test
     * @header Authorization Bearer token
     * 
     * @bodyParam address string 
     * @bodyParam number int
     * @bodyParam city string
     * @bodyParam region string
     * @bodyParam state string
     * @bodyParam zip_code string
     * @bodyParam country_id bigint
     * 
     * @response 200
     * {
     * "data": [
	 *	{
	 *		"id": 3,
	 *		"address": "insulindelaan",
	 *		"city": "wormerveer",
	 *		"region": "Noord-Holland",
	 *		"zip_code": "1521bc",
	 *		"default": false,
	 *		"type": "home",
	 *		"full_name": "joe john",
	 *		"company_name": "CHD",
	 *		"phone_number":"123456789",
	 *		"tax_nr": "132546",
	 *		"lat": null,
	 *		"lng": null,
	 *		"created_at": "2024-05-09T13:29:42.000000Z",
	 *		"updated_at": "2024-05-09T13:29:42.000000Z"
	 *	},
     * ],
     * "status": 200,
	 * "message": null
     * }
     * 
     * @param Request $request
     * @param Country $country
     * @return mixed
     */
    public function search(
        Request $request,
        Country $country
    )
    {
        $search = collect(
            $request->only([
                'address', 'number', 'city', 'region', 'state', 'zip_code', 'country_id'
            ])
        )
            ->filter()
            ->toArray();

        /** @var $results */
        $results = $country->addresses()->where(function ($q) use ($search) {
            return collect($search)->map(function ($keyword, $column) use ($q) {
                $q->where((string)($column), 'LIKE', "%{$keyword}%");

            });
        })->get()->unique(['address']);

        return AddressResource::collection($results)
            ->hide(array_key_exists('number', $search) ? [] : $this->hide)
            ->additional([
                'status' => Response::HTTP_OK,
                'message' => null
            ]);
    }

    /**
     * Store a new address
     * 
     * @header Origin http://{sub_domin}.prindustry.test
     * @header Referer http://{sub_domin}.prindustry.test
     * @header Authorization Bearer token
     * 
     * @response 201 
     *  {
     * 	"data": {
     * 		"id": 6,
     * 		"address": "test",
     * 		"number": 55,
     * 		"city": "cairo",
     * 		"region": null,
     * 		"zip_code": "55",
     * 		"default": false,
     * 		"type": "home",
     * 		"full_name": "joe john",
     * 		"company_name": "CHD",
     * 		"phone_number": "123456789",
     * 		"tax_nr": "123456",
     * 		"lat": null,
     * 		"lng": null,
     * 		"created_at": "2024-05-12T11:13:41.000000Z",
     * 		"updated_at": "2024-05-12T11:13:41.000000Z"
     * 	},
     * 	"status": 200,
     * 	"message": null
     * }
     * 
     * @param StoreAddressRequest $request
     * @param Country             $country
     * @return AddressResource
     */
    public function store(
        StoreAddressRequest $request,
        Country             $country
    )
    {
        $search = collect(
            $request->only([
                'address', 'number', 'city', 'region', 'state', 'zip_code', 'country_id'
            ])
        )
            ->filter()
            ->merge(['country_id' => $country->id])
            ->toArray();

        return AddressResource::make(Address::firstOrCreate($search))
            ->additional([
                'status' => Response::HTTP_OK,
                'message' => null
            ]);
    }
}
