<?php

namespace App\Http\Controllers\Tenant\Mgr\Users\Profile;

use App\Http\Controllers\Controller;
use App\Http\Requests\Profile\ProfileUpdateRequest;
use App\Http\Resources\Profile\ProfileResource;
use App\Models\Tenants\User;
use App\Repositories\ProfileRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class ProfileController extends Controller
{

    /**
     * default hiding field from response
     */
    private array $hide = ['created_at'];

    /**
     * @var ProfileRepository
     */
    protected ProfileRepository $user;

    /**
     * ProfileController constructor.
     * @param User $user
     */
    public function __construct(
        User $user
    )
    {
        $this->user = new ProfileRepository($user);
    }

    /**
     * @OA\Get(
     *   tags={"Profile"},
     *   path="users/{user_id}/profile",
     *   summary="show profile details",
     *   @OA\SecurityScheme(
     *         securityScheme="bearerAuth",
     *         in="header",
     *         name="bearerAuth",
     *         type="oauth2",
     *         scheme="passport",
     *         bearerFormat="JWT",
     *   ),
     *   @OA\Response(response="200", description="success",@OA\JsonContent(ref="#/components/schemas/ProfileResource"))),
     * )
     * @param int $id
     * @return ProfileResource|JsonResponse
     */
    public function show(
        int $id
    )
    {
        $profile = $this->user->show($id);
        /**
         * check if we have user profile exists
         */
        if ($profile) {
            return ProfileResource::make($profile)
                ->hide($this->hide)
                ->additional([
                    'status' => Response::HTTP_OK,
                    'message' => null
                ]);
        }
        /**
         * error response
         */
        return response()->json([
            'message' => __('profiles.not_found'),
            'status' => Response::HTTP_NOT_FOUND
        ], 404);
    }

    /**
     * @OA\Put(
     *   tags={"Profile"},
     *   path="users/{user_id}/profile",
     *   summary="update profile",
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
     *      description="insert profile data",
     *      @OA\JsonContent(ref="#/components/schemas/ProfileUpdateRequest"),
     *   ),
     *   @OA\Response(response="200", description="success",@OA\JsonContent(ref="#/components/schemas/ProfileResource"))),
     * )
     * @param ProfileUpdateRequest $request
     * @param int                  $id
     * @return ProfileResource|JsonResponse
     */
    public function update(
        ProfileUpdateRequest $request,
        int                  $id
    )
    {
        // @todo update form request
        if (
            $this->user->update(
                $id,
                $request->validated()
            )
        ) {
            return ProfileResource::make($this->user->show($id))
                ->hide($this->hide)
                ->additional([
                    'status' => Response::HTTP_OK,
                    'message' => null
                ]);
        }

        /**
         * error response
         */
        return response()->json([
            'data' => [
                'message' => __('profiles.bad_request')
            ]
        ], Response::HTTP_BAD_REQUEST);

    }
}
