<?php

declare(strict_types=1);

namespace App\Http\Controllers\Tenant\Mgr\Teams;

use App\Http\Controllers\Controller;
use App\Http\Requests\Members\DetachTeamMembersRequest;
use App\Http\Requests\Teams\TeamStoreRequest;
use App\Http\Requests\Teams\TeamUpdateRequest;
use App\Http\Resources\Teams\TeamResource;
use App\Models\Tenants\Category;
use App\Models\Tenants\Member;
use App\Models\Tenants\Product;
use App\Models\Tenants\Team;
use App\Models\Tenants\User;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

/**
 * @group Tenant Teams
 */
final class TeamController extends Controller
{
    /**
     * @var int|mixed
     */
    private readonly int $per_page;

    /**
     * TeamsController constructor.
     */
    public function __construct(
        Request $request
    ) {
        /**
         * default number of pages
         */
        $this->per_page = $request->integer('per_page') ?? 10;
    }

    /**
     * Get Teams
     *
     * @header Origin http://{sub_domin}.prindustry.test
     * @header Referer http://{sub_domin}.prindustry.test
     * @header Authorization Bearer token
     *
     * @response 200
     * {
     *  "data": [
     *      {
	 *		"id": 1,
	 *		"name": "administrator",
	 *		"description": null,
	 *		"users": [],
	 *		"address": []
	 *	    },
     *    ],
     * "links":{},
     * "meta":{},
     * "status":200,
     * "message": null
     * }
     *
     * @return AnonymousResourceCollection
     */
    public function index(): AnonymousResourceCollection
    {
        return TeamResource::collection(
            Team::with([
                'addresses.country',
                'users.contexts',
                'members.contexts'
            ])->paginate($this->per_page)
        )
            ->additional([
                'status' => Response::HTTP_OK,
                'message' => null
            ]);
    }

    /**
     * Create Team
     *
     * @header Origin http://{sub_domin}.prindustry.test
     * @header Referer http://{sub_domin}.prindustry.test
     * @header Authorization Bearer token
     *
     * @bodyParam name string required The name of team. Example: newTeam
     * @bodyParam description string The description of team. Example: Team description
     * @bodyParam users array The users ids of team. Example: [1,2,3]
     * @bodyParam address bigint address id of team. Example: 1
     *
     * @response 201
     * {
     * 	"data": {
     * 		"id": 3,
     * 		"name": "new group",
     * 		"description": null,
     * 		"users": [],
     * 		"address": []
     * 	},
     * 	"status": 201,
     * 	"message": "team has been added."
     * }
     *
     * @response 422
     * {
     * 	"message": "The name has already been taken. (and 3 more errors)",
     * 	"errors": {
     * 		"name": [
     * 			"The name has already been taken."
     * 		],
     * 		"address": [
     * 			"The selected address is invalid."
     * 		],
     * 		"users.0": [
     * 			"The selected users.0 is invalid."
     * 		]
     * 	}
     * }
     *
     * @response 400
     * {
     *	"data": {
     *		"status": 400,
     *		"message": "We could'not handel your request, please try again later"
     *	}
     * }
     *
     * @param TeamStoreRequest $request
     *
     * @return TeamResource|JsonResponse
     */
    public function store(
        TeamStoreRequest $request
    ): JsonResponse|TeamResource
    {
        if ($team = Team::create($request->safe()->except(['users', 'address']))) {
            if ($request->get('users')) {
                foreach ($request->get('users') as $user_id) {
                    $team->users()->syncwithoutdetaching($user_id);
                }
            }

            if ($request->address) {
                $team->address()->sync([
                    $request->address => [
                        'type' => $request->address_type,
                        'full_name' => $request->address_full_name,
                        'company_name' => $request->address_company_name,
                        'phone_number' => $request->address_phone_number,
                        'tax_nr' => $request->address_tax_nr,
                        'team_address' => $request->boolean('address_team_address'),
                        'team_id' => $request->address_team_id,
                        'team_name' => $request->address_team_name
                    ]
                ]);
            } else {
                $team->address()->detach($team->getAttribute('id'));
            }

            return TeamResource::make(
                $team->load([
                    'addresses.country',
                    'users.contexts',
                    'members.contexts'
                ])
            )->additional([
                'status' => Response::HTTP_CREATED,
                'message' => __('teams.added')
            ]);
        }

        /**
         * error response
         */
        return response()->json([
            'data' => [
                'status' => Response::HTTP_BAD_REQUEST,
                'message' => __('teams.bad_request')
            ]
        ], Response::HTTP_BAD_REQUEST);
    }

    /**
     * Show Team
     *
     * @header Origin http://{sub_domin}.prindustry.test
     * @header Referer http://{sub_domin}.prindustry.test
     * @header Authorization Bearer token
     *
     * @urlParam id integer required The ID of the team.
     *
     * @response 200
     * {
     * "data": {
     *		"id": 1,
     *		"name": "administrator",
     *		"description": null,
     *		"users": [],
     *		"address": []
     *	},
     *	"status": 200,
     *	"message": "team has been added."
     * }
     *
     */
    public function show(
        Team $team
    ): TeamResource
    {
        return TeamResource::make(
            $team->load([
                'addresses.country',
                'users.contexts',
                'members.contexts'
            ])
        )->additional([
            'status' => Response::HTTP_OK,
            'message' => __('teams.added')
        ]);
    }

    /**
     * Update Team
     *
     * @header Origin http://{sub_domin}.prindustry.test
     * @header Referer http://{sub_domin}.prindustry.test
     * @header Authorization Bearer token
     *
     * @urlParam team_id integer required The ID of the team.
     *
     * @bodyParam name string required The name of team. Example: newTeam
     * @bodyParam description string The description of team. Example: Team description
     * @bodyParam users array The users ids of team. Example: [1,2,3]
     * @bodyParam address bigint address id of team. Example: 1
     *
     * @response 200
     * {
     * 	"data": {
     * 		"id": 3,
     * 		"name": "new group",
     * 		"description": null,
     * 		"users": [],
     * 		"address": []
     * 	},
     * 	"status": 200,
     * 	"message": "team has been updated."
     * }
     *
     * @response 422
     * {
     * 	"message": "The name has already been taken. (and 3 more errors)",
     * 	"errors": {
     * 		"name": [
     * 			"The name has already been taken."
     * 		],
     * 		"address": [
     * 			"The selected address is invalid."
     * 		],
     * 		"users.0": [
     * 			"The selected users.0 is invalid."
     * 		]
     * 	}
     * }
     *
     * @response 400
     * {
     *	"data": {
     *		"status": 400,
     *		"message": "We could'not handel your request, please try again later"
     *	}
     * }
     *
     * @param Team              $team
     * @param TeamUpdateRequest $request
     *
     * @return TeamResource|JsonResponse
     */
    public function update(
        Team              $team,
        TeamUpdateRequest $request
    ): JsonResponse|TeamResource
    {
        if ($team->update($request->safe()->except(['users', 'address']))) {
            if ($request->get('users')) {
                foreach ($request->get('users') as $user_id) {
                    $team->users()->syncwithoutdetaching($user_id);
                }
            }

            if ($request->address) {
                $team->address()->sync([
                    $request->address => [
                        'type' => $request->address_type,
                        'full_name' => $request->address_full_name,
                        'company_name' => $request->address_company_name,
                        'phone_number' => $request->address_phone_number,
                        'tax_nr' => $request->address_tax_nr,
                        'team_address' => $request->boolean('address_team_address'),
                        'team_id' => $request->address_team_id,
                        'team_name' => $request->address_team_name
                    ]
                ]);
            } else {
                $team->address()->detach($team->getAttribute('id'));
            }

            return TeamResource::make(
                $team->load([
                    'addresses.country',
                    'users.contexts',
                    'members.contexts'
                ])
            )->additional([
                'status' => Response::HTTP_OK,
                'message' => __('teams.updated')
            ]);
        }

        /**
         * error response
         */
        return response()->json([
            'data' => [
                'status' => Response::HTTP_BAD_REQUEST,
                'message' => __('teams.bad_request')
            ]
        ], Response::HTTP_BAD_REQUEST);
    }

    /**
     * Delete Team
     *
     * @header Origin http://{sub_domin}.prindustry.test
     * @header Referer http://{sub_domin}.prindustry.test
     * @header Authorization Bearer token
     *
     * @urlParam id integer required The ID of the team.
     *
     * @response 200
     * {
     * 	"data": {
     * 		"status": 200,
     * 		"message": "team has been removed."
     * 	}
     *
     * @response 400
     * {
     *	"data": {
     *		"status": 400,
     *		"message": "We could'not handel your request, please try again later"
     *	}
     * }
     *
     * @param Team $team
     *
     * @return JsonResponse
     * @throws Exception
     */
    public function destroy(
        Team $team
    ): JsonResponse
    {
        if ($team->delete()) {
            return response()->json([
                'data' => [
                    'status' => Response::HTTP_OK,
                    'message' => __('teams.team_removed')
                ]
            ]);
        }

        /**
         * error response
         */
        return response()->json([
            'data' => [
                'status' => Response::HTTP_BAD_REQUEST,
                'message' => __('teams.bad_request')
            ]
        ], Response::HTTP_BAD_REQUEST);
    }

    /**
     * Delete a user from the team
     *
     * @header Origin http://{sub_domin}.prindustry.test
     * @header Referer http://{sub_domin}.prindustry.test
     * @header Authorization Bearer token
     *
     * @urlParam team_id integer required The ID of the team.
     * @urlParam user_id integer required The ID of the user.
     *
     * @response 200
     * {
     *   "message": "Model has been removed successfully.",
     *   "status": 200
     * }
     *
     * @response 404
     * {
     *   "message": "No model found with the giving id.",
     *   "status": 200
     * }
     *
     * @param Team $team
     * @param User $user
     * @return JsonResponse
     */
    public function userDetaching(
        Team $team,
        User $user
    ): JsonResponse
    {
        if($team->users()->detach($user->getAttribute('id'))) {
            return response()->json([
                'message' => __('User has been detached from the team successfully'),
                'status' => Response::HTTP_OK
            ]);
        }

        return response()->json([
            'message' => __('Could not detach the user from the team'),
            'status' => Response::HTTP_UNPROCESSABLE_ENTITY
        ],
            Response::HTTP_UNPROCESSABLE_ENTITY
        );
    }

    /**
     * Detach multiple members from the team.
     *
     * @header Origin http://{sub_domain}.example.test
     * @header Referer http://{sub_domain}.example.test
     * @header Authorization Bearer {token}
     *
     * @urlParam team integer required The ID of the team. Example: 2
     *
     * @bodyParam ids array required List of user IDs to detach. Example: [3, 5, 8]
     * @bodyParam ids.* integer Each user ID must be a valid existing user.
     *
     * @response 200 {
     *   "message": "Selected members have been detached from the team successfully.",
     *   "status": 200
     * }
     *
     * @response 422 {
     *   "message": "No valid members found to detach.",
     *   "status": 422
     * }
     *
     * @param DetachTeamMembersRequest $request
     * @param Team $team
     * @return JsonResponse
     */
    public function memberDetaching(
        DetachTeamMembersRequest $request,
        Team $team
    ): JsonResponse {
        $userIds = $request->validated('ids', []);
        // Detach members
        $team->users()->detach($userIds);

        return response()->json([
            'message' => __(' :count member(s) detached successfully from team :team .', [
                'count' => count($userIds),
                'team' => $team->name
            ]),
            'status' => Response::HTTP_OK,
        ]);
    }
}
