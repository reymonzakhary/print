<?php

namespace App\Http\Controllers\Tenant\Mgr\Teams\Accessibility;

use App\Http\Controllers\Controller;
use App\Http\Requests\Teams\StoreAccessibilityRequest;
use App\Http\Requests\Teams\DetachTeamCategoriesRequest;
use App\Http\Resources\Teams\TeamAccessibilityResource;
use App\Models\Tenants\Category;
use App\Models\Tenants\Product;
use App\Models\Tenants\Team;
use App\Models\Tenants\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Symfony\Component\HttpFoundation\Response;

/**
 * @group Tenant Teams
 *
 * @subgroup Tenant Team Accessibility
 */
class AccessibilityController extends Controller
{

    /**
     * Get Team Accessibility
     *
     * return categories and products of team
     *
     * @header Origin http://{sub_domin}.prindustry.test
     * @header Referer http://{sub_domin}.prindustry.test
     * @header Authorization Bearer token
     *
     * @urlParam team_id integer required The ID of the team.
     *
     * @response 200
     * {
     * "data": {
	 *   	"categories": [],
	 *   	"products": []
	 *   },
	 *   "message": null,
	 *   "status": 200
     * }
     *
     *
     * @param Team $team
     * @return TeamAccessibilityResource
     */
    public function index(
        Team $team
    )
    {
        return TeamAccessibilityResource::make(
            $team->load('category', 'product')
        )->additional([
            'message' => null,
            'status' => Response::HTTP_OK,
        ]);
    }

    /**
     * Store Accessibility
     *
     * store category or product
     *
     * @header Origin http://{sub_domin}.prindustry.test
     * @header Referer http://{sub_domin}.prindustry.test
     * @header Authorization Bearer token
     *
     * @urlParam team_id integer required The ID of the team.
     *
     * @bodyParam model string required The name of model. Example: category or product or users
     * @bodyParam model_id string required the id of the model. Example: 1
     *
     * @response 200
     * {
     * "data": {
	 *   	"categories": [],
	 *   	"products": []
	 *   },
	 *   "message": null,
	 *   "status": 200
     * }
     *
     * @response 422
     * {
     *  "message": "The selected model is invalid.",
	 *   "errors": {
	 *   	"model": [
	 *   		"The selected model is invalid."
	 *   	]
	 *   }
     * }
     * @param StoreAccessibilityRequest $request
     * @param Team                      $team
     * @return TeamAccessibilityResource
     */
    public function store(
        StoreAccessibilityRequest $request,
        Team $team
    )
    {
        is_array($team->{$request->model}())?:$team->{$request->model}()->syncWithoutDetaching($request->model_id);

        return TeamAccessibilityResource::make($team)->additional([
            'message' => __("The {$request->model_id} has been added successfully."),
            'status' => Response::HTTP_OK,
        ]);

    }

    /**
     * Delete User
     *
     * Delete User from team
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
        if($team->users()->detach($user->id)) {
            return response()->json([
                'message' => __('Model has been removed successfully.'),
                'status' => Response::HTTP_OK
            ], Response::HTTP_OK);
        }

        return response()->json([
            'message' => __('No model found with the giving id.'),
            'status' => Response::HTTP_NOT_FOUND
        ], Response::HTTP_NOT_FOUND);

    }

    /**
     * Detach multiple categories from the team.
     *
     * @header Origin http://{sub_domain}.prindustry.test
     * @header Referer http://{sub_domain}.prindustry.test
     * @header Authorization Bearer token
     *
     * @urlParam team_id integer required The ID of the team.
     *
     * @bodyParam ids array required List of category IDs to detach. Example: ["1", "2", "3"]
     * @bodyParam ids.* string Each category ID must be a valid string.
     *
     * @response 200
     * {
     *   "message": "3 category(ies) detached successfully from team Team Name.",
     *   "status": 200
     * }
     *
     * @response 422
     * {
     *   "message": "Please specify one or more category IDs to detach.",
     *   "status": 422
     * }
     *
     * @param DetachTeamCategoriesRequest $request
     * @param Team $team
     * @return JsonResponse
     */
    public function categoriesDetaching(DetachTeamCategoriesRequest $request, Team $team): JsonResponse {
        $ids = $request->validated('ids', []);
        $regular = array_filter($ids, fn($id) => is_numeric($id));
        $external = array_filter($ids, fn($id) => !is_numeric($id));
        
        $detached = 0;
        if (!empty($regular)) $detached += $team->category()->detach($regular);
        if (!empty($external)) $detached += $team->externalCategory()->detach($external);
        
        return response()->json([
            'message' => __(':count category(ies) detached successfully from team :team.', [
                'count' => $detached, 'team' => $team->name
            ]),
            'status' => Response::HTTP_OK,
        ]);
    }

    /**
     * Delete Product
     *
     * Delete Product from team
     *
     * @header Origin http://{sub_domin}.prindustry.test
     * @header Referer http://{sub_domin}.prindustry.test
     * @header Authorization Bearer token
     *
     * @urlParam team_id integer required The ID of the team.
     * @urlParam product_row_id integer required The Row ID of the product.
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
     * @param Team    $team
     * @param Product $product
     * @return JsonResponse
     */
    public function productDetaching(
        Team $team,
        Product $product,
    ): JsonResponse
    {
        if($team->users()->detach($product->row_id)) {
            return response()->json([
                'message' => __('Model has been removed successfully.'),
                'status' => Response::HTTP_OK
            ], Response::HTTP_OK);
        }

        return response()->json([
            'message' => __('No model found with the giving id.'),
            'status' => Response::HTTP_NOT_FOUND
        ], Response::HTTP_NOT_FOUND);
    }
}
