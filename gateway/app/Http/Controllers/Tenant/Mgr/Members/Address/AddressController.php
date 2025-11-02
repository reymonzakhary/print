<?php

namespace App\Http\Controllers\Tenant\Mgr\Members\Address;

use App\Foundation\Settings\Settings;
use App\Http\Controllers\Controller;
use App\Http\Requests\Addresses\StoreMemberAddressRequest;
use App\Http\Requests\Addresses\UpdateMemberAddressRequest;
use App\Http\Resources\Address\AddressResource;
use App\Models\Tenants\Address;
use App\Models\Tenants\Member;
use App\Repositories\AddressRepository;
use App\Repositories\UserRepository;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

/**
 * @group Tenant Members
 *
 * @subgroup Tenant Members Addresses
 */
final class AddressController extends Controller
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
     * @var UserRepository
     */
    protected UserRepository $member;

    /**
     * AddressController constructor.
     * @param Address $address
     * @param Member    $member
     */
    public function __construct(
        Address $address,
        Member    $member
    )
    {
        $this->member = new UserRepository($member);
        $this->address = new AddressRepository($address);
    }

    /**
     * List Member Addresses
     *
     * @header Origin http://{sub_domin}.prindustry.test
     * @header Referer http://{sub_domin}.prindustry.test
     * @header Authorization Bearer token
     *
     *
     * @param Member $member
     * @return mixed
     */
    public function index(
        Member $member
    ): mixed
    {
        if (request()->input('shop')) {
            if ((int) Settings::useTeamAddress()) {
                $addresses = $member->userTeams->map(function ($team) {
                    return $team->address()->with('country')->get();
                })->flatten();
            } else {
                $addresses = $member->userTeams->map(function ($team) {
                    return $team->address()->with('country')->get();
                })->flatten()->merge($member->addresses()->with('country')->get())->flatten();
            }
        }else{
            $addresses = $member->addresses()->with('country')->get();
        }
        return AddressResource::collection($addresses)
            ->hide($this->hide)
            ->additional([
                'status' => Response::HTTP_OK,
                'message' => null
            ]);
    }

    /**
     * Store Member Address
     *
     * @header Origin http://{sub_domin}.prindustry.test
     * @header Referer http://{sub_domin}.prindustry.test
     * @header Authorization Bearer token
     *
     * @urlParam user integer required The ID of the member.
     *
     * @response 201
     * {
     * 	"data": {
     * 		"id": 3,
     * 		"address": "test street",
     * 		"number": "20        ",
     * 		"city": "cairo",
     * 		"region": "cairo",
     * 		"zip_code": "1521bc",
     * 		"default": true,
     * 		"type": "home",
     * 		"full_name": "Reymon Zakhary",
     * 		"company_name": "CHD",
     * 		"phone_number": "123456789",
     * 		"tax_nr": "NL245564749B02",
     * 		"lat": null,
     * 		"lng": null,
     * 		"created_at": "2024-05-09T13:29:42.000000Z",
     * 		"updated_at": "2024-05-09T13:29:42.000000Z"
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
     * @param StoreMemberAddressRequest $request
     * @param Member          $member
     * @return AddressResource
     */
    public function store(
        StoreMemberAddressRequest $request,
        Member         $member
    ): AddressResource
    {
        return AddressResource::make(
            $request->address
        )
            ->hide($this->hide)
            ->additional([
                'status' => Response::HTTP_CREATED,
                'message' => _('Address has been created successfully.')
            ]);
    }

    /**
     * Update Member Address
     *
     * @header Origin http://{sub_domin}.prindustry.test
     * @header Referer http://{sub_domin}.prindustry.test
     * @header Authorization Bearer token
     *
     * @response 201
     * {
     *    "data": {
     *        "id": 5,
     *        "address": "valkstraat",
     *        "number": "8",
     *        "city": "zaandijk",
     *        "region": cairo,
     *        "zip_code": "1505cd",
     *        "default": false,
     *        "type": home,
     *        "full_name": joe john,
     *        "company_name": CHD,
     *        "phone_number": 123456789,
     *        "tax_nr": 123456,
     *        "lat": null,
     *        "lng": null,
     *        "created_at": "2024-05-09T14:06:15.000000Z",
     *        "updated_at": "2024-05-09T14:06:15.000000Z"
     *    },
     *    "status": 200,
     *    "message": null
     * }
     *
     * @response 400
     * {
     *    "data": {
     *        "message": "We could'not handel your request, please try again later"
     *    }
     * }
     *
     * @response 422
     * {
     *    "message": "The number field is required. (and 1 more error)",
     *    "errors": {
     *        "number": [
     *            "The number field is required."
     *        ],
     *        "country_id": [
     *            "The country id field is required."
     *        ]
     *    }
     * }
     *
     * @param Member $member
     * @param Address $address
     * @param UpdateMemberAddressRequest $request
     * @return JsonResponse
     */
    public function update(
        Member               $member,
        Address              $address,
        UpdateMemberAddressRequest $request
    ): AddressResource
    {
        return AddressResource::make($request->address)
            ->additional([
                'message' => __('Address has been updated successfully.'),
                'status' => Response::HTTP_OK,
            ]);
    }

    /**
     * Delete Member Address
     *
     * @header Origin http://{sub_domin}.prindustry.test
     * @header Referer http://{sub_domin}.prindustry.test
     * @header Authorization Bearer token
     *
     * @response 202
     * {
     * 	"data": {
     * 		"message": "address has been removed."
     * 	}
     * }
     * @param Member    $member
     * @param Address $address
     * @return JsonResponse
     */
    public function destroy(
        Member    $member,
        Address $address
    ): JsonResponse
    {
        if ($member->addresses()->detach($address->id)) {
            return response()->json([
                'message' => __('Address has been deleted successfully.'),
                'status' => Response::HTTP_OK,
            ], Response::HTTP_ACCEPTED);
        }

        /**
         * error response
         */
        return response()->json([
            'data' => [
                'message' => __('We are unable to process your request.'),
            ]
        ], Response::HTTP_BAD_REQUEST);
    }
}
