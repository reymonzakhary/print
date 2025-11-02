<?php

namespace App\Http\Controllers\Tenant\Mgr\Members;

use App\Http\Controllers\Controller;
use App\Http\Requests\Members\StoreMemberRequest;
use App\Http\Requests\Members\UpdateMemberRequest;
use App\Http\Resources\Members\MemberResource;
use App\Http\Resources\Orders\OrderResource;
use App\Http\Resources\Orders\TrashedOrderResource;
use App\Http\Resources\Quotations\QuotationResource;
use App\Http\Resources\Users\UserResource;
use App\Models\Tenants\Context;
use App\Models\Tenants\Member;
use App\Models\Tenants\User;
use App\Repositories\UserRepository;
use App\Scoping\Scopes\Members\MemberOnlyTrashedScope;
use App\Scoping\Scopes\Members\MemberTypeScope;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

/**
 * @group Tenant Members
 */
final class MemberController extends Controller
{
    /**
     * @var Context|null
     */
    protected ?Context $context;

    /**
     * @var int|mixed
     */
    protected $per_page = 10;

    /**
     * default hiding field from response
     */
    protected array $hide;

    /**
     * MemberController constructor.
     */
    protected UserRepository $user;

    public function __construct(Member $user)
    {
        $this->user = new UserRepository($user);

        /**
         * default hidden field
         */
        $this->hide = [
            request()->get('include_profile') ?? 'profile',
            'config'
        ];

        /**
         * default number of pages
         */
        $this->per_page = request()->get('per_page') ?? $this->per_page;
    }

    /**
     * List Members
     *
     * @header Origin http://{sub_domin}.prindustry.test
     * @header Referer http://{sub_domin}.prindustry.test
     * @header Authorization Bearer token
     *
     * @response 200
     * {
     *  "data": [
     *      {
     *        "id": 2,
     *        "owner": false,
     *        "email": "test@charisma-design.eu",
     *        "email_verified_at": null,
     *        "created_at": "2024-05-09T11:32:10.000000Z",
     *        "updated_at": "2024-05-09T11:32:10.000000Z",
     *        "ctx": [
     *            {
     *                "id": 1,
     *                "name": "mgr",
     *                "description": null,
     *                "member": true
     *            }
     *        ],
     *        "permission": [],
     *        "roles": [],
     *        "teams": [],
     *        "companies": [],
     *        "addresses": []
     *        },
     *    ],
     * "links":{},
     * "meta":{},
     * "status":200,
     * "message": null
     * }
     *
     * @response 200
     * {
     *  "message": "There is no users.",
     *  "data": [],
     *  "status":200
     * }
     *
     * @return mixed
     */
    public function index()
    {
        $users = $this->user->all($this->per_page, member: true, scopes: $this->scopes());

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
         * No users list has been found
         */
        return response()->json([
            'message' => __('There is no users.'),
            'data' => [],
            'status' => Response::HTTP_OK,
        ], Response::HTTP_OK);
    }

    /**
     * Show Member
     *
     * @header Origin http://{sub_domin}.prindustry.test
     * @header Referer http://{sub_domin}.prindustry.test
     * @header Authorization Bearer token
     *
     * @urlParam user integer required The ID of the member.
     *
     * @response 200
     * {
     *  "data": {
     *        "id": 2,
     *        "owner": false,
     *        "email": "test@charisma-design.eu",
     *        "email_verified_at": null,
     *        "created_at": "2024-05-09T11:32:10.000000Z",
     *        "updated_at": "2024-05-09T11:32:10.000000Z",
     *        "ctx": [
     *            {
     *                "id": 1,
     *                "name": "mgr",
     *                "description": null,
     *                "member": true
     *            }
     *        ],
     *        "permission": [],
     *        "roles": [],
     *        "teams": [],
     *        "companies": [],
     *        "addresses": []
     *        },
     * "status":200,
     * "message": null
     * }
     *
     * @response 404
     * {
     *  "status":404
     * }
     *
     * @param int $id
     * @return UserResource|JsonResponse
     */
    public function show(int $id)
    {
        $user = $this->user->show($id, member: true);
        /**
         * check if we have users
         */
        if ($user) {
            return UserResource::make($user)
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
            'message' => __('User not exist.'),
            'status' => Response::HTTP_NOT_FOUND
        ], Response::HTTP_NOT_FOUND);
    }

    /**
     * Store Member
     *
     * @header Origin http://{sub_domin}.prindustry.test
     * @header Referer http://{sub_domin}.prindustry.test
     * @header Authorization Bearer token
     *
     * @response 201
     * {
     *    "data": {
     *        "id": 2,
     *        "username": null,
     *        "email": "test@charisma-design.eu",
     *        "email_verified_at": null,
     *        "created_at": "2024-05-09T11:32:10.000000Z",
     *        "updated_at": "2024-05-09T11:32:10.000000Z",
     *        "roles": [],
     *        "teams": [],
     *        "companies": [],
     *        "addresses": []
     *    },
     *    "status": 200,
     *    "message": null,
     *    "password": "?O7qy%1$uWz8PmH6ds1281018668"
     * }
     *
     * @response 422
     * {
     *    "message": "The first name field is required. (and 2 more errors)",
     *    "errors": {
     *        "first_name": [
     *            "The first name field is required."
     *        ],
     *        "last_name": [
     *            "The last name field is required."
     *        ],
     *        "email": [
     *            "The email field is required."
     *        ]
     *    }
     * }
     *
     * @param StoreMemberRequest $request
     * @return MemberResource
     */
    public function store(
        StoreMemberRequest $request
    ) {
        $password = $request->password;
        return MemberResource::make(
            $this->user->create($request->validated())
        )->hide(
            $this->hide
        )->additional([
            'status' => Response::HTTP_OK,
            'message' => null,
            'password' => $password
        ]);
    }

    /**
     * Update Member
     *
     * @header Origin http://{sub_domin}.prindustry.test
     * @header Referer http://{sub_domin}.prindustry.test
     * @header Authorization Bearer token
     *
     * @urlParam user integer required The ID of the member.
     *
     * @response 400
     * {
     *    "message": "We could'not handel your request, please try again later",
     *    "status": 400
     * }
     *
     *
     * @param UpdateMemberRequest $request
     * @param int $id
     * @return MemberResource|JsonResponse
     */
    public function update(
        UpdateMemberRequest $request,
        int $id
    ) {
        if ($this->user->update(
            $id,
            $request->validated(),
            member: true
        )) {
            return MemberResource::make(
                $this->user->show($id, member: true)
            )->hide($this->hide)
                ->additional([
                    'status' => Response::HTTP_OK,
                    'message' => null
                ]);
        }

        /**
         * error response
         */
        return response()->json([
            'message' => __('User does not exist'),
            'status' => Response::HTTP_NOT_FOUND
        ], 404);
    }

    /**
     * @param Member $member
     * @return JsonResponse
     */
    public function verification(
        Member $member
    ): JsonResponse
    {
        if ($member->hasVerifiedEmail()) {
            return response()->json([
                "message" => 'Member already have verified email!',
                "status" => Response::HTTP_UNPROCESSABLE_ENTITY
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $shouldPasswordGenerated = 0 === strlen($member->getAttribute('password'));
        $member->sendApiEmailVerificationNotification(tenant()->uuid, $shouldPasswordGenerated);

        return response()->json([
            "message" => 'The notification has been resubmitted',
            "status" => Response::HTTP_OK
        ], Response::HTTP_OK);
    }

    /**
     * Delete Member
     *
     * @OA\Delete(
     *   tags={"Members"},
     *   path="members/{id}",
     *   summary="delete member",
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
     *    description="member deleted with success",
     *    @OA\JsonContent(
     *       @OA\Property(property="message", type="string", example="member removed"),
     *   )
     * ),
     * )
     *
     * @param Member $member
     *
     * @return JsonResponse
     */
    public function destroy(
        Member $member
    ): JsonResponse
    {
        if ($member->orders()->count() > 0 || $member->quotations()->count() > 0) {
            return response()->json([
                'data' => [
                    'orders' => OrderResource::collection($member->orders()->get()),
                    'quotations' => QuotationResource::collection($member->quotations()->get())
                ],

                'message' => __(
                    'We could not remove this member, member has one or more orders/quotations related to it!'
                ),

                'status' => Response::HTTP_UNPROCESSABLE_ENTITY
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        if (!$member->delete()) {
            return response()->json([
                'message' => __('We couldn\'t remove the requested member!'),
                'status' => Response::HTTP_UNPROCESSABLE_ENTITY
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        return response()->json([
            'message' => __('Member has been removed successfully!'),
            'status' => Response::HTTP_OK
        ], Response::HTTP_OK);
    }


    /**
     * Restore Member
     *
     * @OA\Restore(
     *   tags={"Members"},
     *   path="members/{id}/restore",
     *   summary="restore deleted member",
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
     *    description="member restored with success",
     *    @OA\JsonContent(
     *       @OA\Property(property="message", type="string", example="member restored"),
     *   )
     * ),
     * )
     *
     * @param int $id
     *
     * @return JsonResponse
     */

    public function restore(
        int $id
    ): JsonResponse
    {
        if (!Member::withTrashed()->findOrFail($id)->restore()) {
            return response()->json([
                'message' => __('We couldn\'t restore the requested member!'),
                'status' => Response::HTTP_UNPROCESSABLE_ENTITY
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        return response()->json([
            'message' => __('Member has been restored successfully!'),
            'status' => Response::HTTP_OK
        ], Response::HTTP_OK);
    }

    /**
     * Get Scopes
     *
     * @return array
     */
    private function scopes(): array
    {
        return [
            'type' => new MemberTypeScope(),
            'trashed' => new MemberOnlyTrashedScope(),
        ];
    }
}
