<?php

namespace App\Http\Controllers\Tenant\Mgr\Users;

use App\Http\Controllers\Controller;
use App\Http\Requests\Users\StoreUserRequest;
use App\Http\Requests\Users\UpdateUserRequest;
use App\Http\Resources\Users\UserResource;
use App\Models\Tenant\User;
use App\Repositories\UserRepository;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use LogicException;
use RuntimeException;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class UserController extends Controller
{

    /**
     * @var UserRepository
     */
    protected UserRepository $user;

    /**
     * default hiding field from response
     */
    protected array $hide;

    /**
     * default total result in one page
     */
    protected int $per_page = 10;

    /**
     * UserController constructor.
     * @param Request $request
     * @param User    $user
     */
    public function __construct(
        Request $request,
        User    $user
    )
    {
        $this->user = new UserRepository($user);
        /**
         * default hidden field
         */
        $this->hide = [
            $request->get('include_profile') ?? 'profile',
            'config'
        ];

        /**
         * default number of pages
         */
        $this->per_page = $request->get('per_page') ?? $this->per_page;
    }

    /**
     * @OA\Get(
     *   tags={"Users"},
     *   path="users",
     *   summary="list all users",
     *   @OA\SecurityScheme(
     *         securityScheme="bearerAuth",
     *         in="header",
     *         name="bearerAuth",
     *         type="oauth2",
     *         scheme="passport",
     *         bearerFormat="JWT",
     *   ),
     * @OA\Response(
     *     response="200", description="success",
     *     @OA\JsonContent(
     *          @OA\Property(type="array", property="data", @OA\Items(ref="#/components/schemas/UserResource")),
     * @OA\Property(type="string", title="message", description="message", property="message", example=""),
     * @OA\Property(type="string", title="status", description="status", property="status", example="200"),
     *      )),
     * )
     * @return JsonResponse | UserResource
     */
    public function index()
    {
        $users = $this->user->all($this->per_page);

        /**
         * check if we have users
         */
        if ($users->items()) {
            return UserResource::collection($users)->hide(
                $this->hide
            )->additional([
                'status' => Response::HTTP_OK,
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
     * @OA\Get(
     *   tags={"Users"},
     *   path="users/{id}",
     *   summary="show user details",
     *   @OA\SecurityScheme(
     *         securityScheme="bearerAuth",
     *         in="header",
     *         name="bearerAuth",
     *         type="oauth2",
     *         scheme="passport",
     *         bearerFormat="JWT",
     *   ),
     *   @OA\Response(response="200", description="success",@OA\JsonContent(ref="#/components/schemas/UserResource"))),
     * )
     * @param int $id
     * @return UserResource|JsonResponse
     */
    public function show(
        int $id
    )
    {
        $user = $this->user->show($id);
        /**
         * check if we have users
         */
        if ($user) {
            return UserResource::make($user)->hide(
                $this->hide
            )->additional([
                'status' => Response::HTTP_OK,
                'message' => null
            ]);
        }
        /**
         * error response
         */
        return response()->json([
            'message' => __('users.not_found'),
            'status' => Response::HTTP_NOT_FOUND
        ], 404);
    }

    /**
     * @OA\Post(
     *   tags={"Users"},
     *   path="users",
     *   summary="create users",
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
     *      description="insert users data",
     *      @OA\JsonContent(ref="#/components/schemas/StoreUserRequest"),
     *   ),
     *   @OA\Response(response="200", description="success",@OA\JsonContent(ref="#/components/schemas/UserResource"))),
     * )
     * @param StoreUserRequest $request
     * @return UserResource
     */
    public function store(
        StoreUserRequest $request
    )
    {
        return UserResource::make(
            $this->user->create(
                $request->validated()
            )
        )
            ->hide($this->hide)
            ->additional([
                'status' => Response::HTTP_CREATED,
                'message' => null,
                'password' => $request->password
            ]);
    }

    /**
     * @param User $user
     *
     * @return JsonResponse
     */
    public function verification(
        User $user
    ): JsonResponse
    {
        if ($user->hasVerifiedEmail()) {
            return response()->json([
                "message" => 'User already have verified email!',
                "status" => Response::HTTP_UNPROCESSABLE_ENTITY
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $shouldPasswordGenerated = 0 === strlen($user->getAttribute('password'));
        $user->sendApiEmailVerificationNotification(tenant()->uuid, $shouldPasswordGenerated);

        return response()->json([
            "message" => 'The notification has been resubmitted',
            "status" => Response::HTTP_OK
        ]);
    }

    /**
     * @OA\Put(
     *   tags={"Users"},
     *   path="users/{id}",
     *   summary="update user data",
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
     *      description="insert users data",
     *      @OA\JsonContent(ref="#/components/schemas/UpdateUserRequest"),
     *   ),
     *   @OA\Response(response="200", description="success",@OA\JsonContent(ref="#/components/schemas/UserResource"))),
     * )
     * @param UpdateUserRequest $request
     * @param int               $id
     * @return UserResource|JsonResponse
     */
    public function update(
        UpdateUserRequest $request,
        int               $id
    )
    {
        if (
            $this->user->update(
                $id,
                $request->validated(),
            )
        ) {
            return UserResource::make($this->user->show($id))
                ->hide($this->hide)
                ->additional([
                    'status' => Response::HTTP_OK,
                    'message' => __('User has been updated successfully!')
                ]);
        }

        /**
         * error response
         */
        return response()->json([
            'message' => __('users.bad_request'),
            'status' => Response::HTTP_BAD_REQUEST

        ], Response::HTTP_BAD_REQUEST);
    }

    /**
     * @OA\Delete(
     *   tags={"Users"},
     *   path="users/{id}",
     *   summary="delete user",
     *   @OA\SecurityScheme(
     *         securityScheme="bearerAuth",
     *         in="header",
     *         name="bearerAuth",
     *         type="oauth2",
     *         scheme="passport",
     *         bearerFormat="JWT",
     *   ),
     * @OA\Response(
     *    response=200,
     *    description="user deleted with success",
     *    @OA\JsonContent(
     *       @OA\Property(property="message", type="string", example="user removed"),
     *   )
     * ),
     * )
     * @param User $user
     * @return JsonResponse
     */
    public function destroy(
        User $user
    ): JsonResponse
    {
        try {
            if ($user->isOwner()) {
                throw new LogicException(
                    __('Owner user cannot be removed from the system')
                );
            }

            if ($user->orders()->count() > 0 || $user->quotations()->count() > 0) {
                throw new LogicException(
                    __(
                        'We could not remove this user, user has one or more orders/quotations related to it!'
                    )
                );
            }

            $user->deleteOrFail();

            return response()->json([
                'message' => __('User has been removed successfully!'),
                'status' => Response::HTTP_OK
            ]);
        } catch (Throwable $e) {
            $statusCode = $e->getCode() ?: Response::HTTP_UNPROCESSABLE_ENTITY;

            return response()->json([
                'status' => $statusCode,
                'message' => $e->getMessage()
            ], $statusCode);
        }
    }
}
