<?php

namespace App\Http\Controllers\Tenant\Mgr\Users\Address;

use App\Foundation\Settings\Settings;
use App\Http\Controllers\Controller;
use App\Http\Requests\Addresses\StoreAddressRequest;
use App\Http\Requests\Addresses\StoreUserAddressRequest;
use App\Http\Requests\Addresses\UpdateAddressRequest;
use App\Http\Requests\Addresses\UpdateUserAddressRequest;
use App\Http\Resources\Address\AddressResource;
use App\Models\Tenants\Address;
use App\Models\Tenants\Team;
use App\Models\Tenants\User;
use App\Repositories\AddressRepository;
use App\Repositories\UserRepository;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

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
    protected UserRepository $user;

    /**
     * AddressController constructor.
     * @param Address $address
     * @param User    $user
     */
    public function __construct(
        Address $address,
        User    $user
    )
    {
        $this->user = new UserRepository($user);
        $this->address = new AddressRepository($address);
    }

    /**
     * @OA\Get(
     *   tags={"Users"},
     *   path="users/{user_id}/addresses",
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
     * @param User $user
     * @return mixed
     */
    public function index(
        User $user
    ): mixed
    {
        if (request()->get('shop')) {
            if ((int) Settings::useTeamAddress()) {
                $addresses = $user->userTeams->map(function ($team) {
                    return $team->address()->with('country')->get();
                })->flatten();
            } else {
                $addresses = $user->userTeams->map(function ($team) {
                    return $team->address()->with('country')->get();
                })->flatten()->merge($user->addresses()->with('country')->get())->flatten();
            }
        }else{
            $addresses = $user->addresses()->with('country')->get();
        }

        return AddressResource::collection(
            $addresses
        )
            ->hide($this->hide)
            ->additional([
                'status' => Response::HTTP_OK,
                'message' => null
            ]);
    }

    /**
     * @OA\Post (
     *   tags={"Users"},
     *   path="users/{user_id}/addresses",
     *   summary="create a new address for the user",
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
     * @param StoreUserAddressRequest $request
     * @param User $user
     * @return AddressResource
     */
    public function store(
        StoreUserAddressRequest $request,
        User                    $user
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
     * @OA\Put (
     *   tags={"Users"},
     *   path="users/{user_id}/addresses/{id}",
     *   summary="update an address for the user",
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
     * @param UpdateUserAddressRequest $request
     * @param User $user
     * @param Address $address
     * @return AddressResource
     */
    public function update(
        UpdateUserAddressRequest $request,
        User                     $user,
        Address                  $address,
    ): AddressResource
    {
        return AddressResource::make($request->address)
            ->additional([
                'message' => __('Address has been updated successfully.'),
                'status' => Response::HTTP_OK,
            ]);
    }

    /**
     * @OA\Delete (
     *   tags={"Users"},
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
     * @param User    $user
     * @param Address $address
     * @return JsonResponse
     */
    public function destroy(
        User    $user,
        Address $address
    ): JsonResponse
    {
        if (
            $user->addresses()->detach($address->id)
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
