<?php

declare(strict_types=1);

namespace App\Http\Controllers\Tenant\Mgr\Companies\Teams;

use App\Http\Controllers\Controller;
use App\Http\Requests\Teams\TeamStoreRequest;
use App\Http\Requests\Teams\TeamUpdateRequest;
use App\Http\Resources\Teams\TeamResource;
use App\Models\Tenant\Company;
use App\Models\Tenant\Team;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Symfony\Component\HttpFoundation\Response;

/**
 * @group Tenant Companies
 *
 * @subgroup Tenant Company Teams
 * @subgroupDescription
 */
final class TeamController extends Controller
{
 /**
     * List company teams
     *
     * @header Origin http://{sub_domin}.prindustry.test
     * @header Referer http://{sub_domin}.prindustry.test
     * @header Authorization Bearer token
     *
     * @param Company $company
     * @return AnonymousResourceCollection
     */
    public function index(
        Company $company
    ): AnonymousResourceCollection
    {
        return TeamResource::collection($company->teams()->paginate(10))
            ->additional([
                'status' => Response::HTTP_OK,
                'message' => null
            ]);
    }

    /**
     *  Show company team
     *
     * @header Origin http://{sub_domin}.prindustry.test
     * @header Referer http://{sub_domin}.prindustry.test
     * @header Authorization Bearer token
     *
     * @param Company $company
     * @param int $teamId
     * @return TeamResource
     */
    public function show(
        Company $company,
        int $teamId
    ): TeamResource
    {
        return TeamResource::make($company->teams()->findOrFail($teamId))
            ->additional([
                'status' => Response::HTTP_OK,
                'message' => null
            ]);
    }

    /**
     * Store company team
     *
     * @header Origin http://{sub_domin}.prindustry.test
     * @header Referer http://{sub_domin}.prindustry.test
     * @header Authorization Bearer token
     *
     * @param Company $company
     * @param TeamStoreRequest $request
     * @return TeamResource
     */
    public function store(
        Company $company,
        TeamStoreRequest $request,
    ): TeamResource
    {
        return TeamResource::make($company->teams()->create($request->validated()))
            ->additional([
                'status' => Response::HTTP_CREATED,
                'message' => __('team has been added.')
            ]);
    }

    /**
     * Update company team
     *
     * @header Origin http://{sub_domin}.prindustry.test
     * @header Referer http://{sub_domin}.prindustry.test
     * @header Authorization Bearer token
     *
     *
     * @param Company $company
     * @param Team $team
     * @param TeamUpdateRequest $request
     * @return TeamResource
     */
    public function update(
        Company $company,
        Team $team,
        TeamUpdateRequest $request,
    ): TeamResource
    {
        return TeamResource::make($team->update($request->validated()))
            ->additional([
                'status' => Response::HTTP_OK,
                'message' => __('team has been updated.')
            ]);
    }

    /**
     * Delete company team
     *
     * @header Origin http://{sub_domin}.prindustry.test
     * @header Referer http://{sub_domin}.prindustry.test
     * @header Authorization Bearer token
     *
     * @param Company $company
     * @param Team $team
     * @return JsonResponse
     */
    public function destroy(
        Company $company,
        Team $team
    ): JsonResponse
    {
        if(!$team->delete()) {
            return response()->json(['message' => __('We could\'not handel your request, please try again later'), 'status' => Response::HTTP_BAD_REQUEST], Response::HTTP_BAD_REQUEST);
        }

        return response()->json(['message' => __('team has been removed.'), 'status' => Response::HTTP_ACCEPTED], Response::HTTP_ACCEPTED);
    }
}
