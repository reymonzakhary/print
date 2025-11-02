<?php

namespace App\Http\Controllers\Tenant\Mgr\Acl\Teams;

use App\Http\Controllers\Controller;
use App\Http\Requests\Acl\StoreMediaToTeamRequest;
use App\Http\Resources\MediaSources\MediaSourceResource;
use App\Models\Tenants\Media\MediaSource;
use App\Models\Tenants\Team;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class TeamAclController extends Controller
{

    /**
     * @OA\Get(
     *   tags={"Team Acl Controller & Media Sources"},
     *   path="/api/v1/mgr/acl/teams/{team}/media-sources",
     *   summary="Get Team Media Sources",
     *   @OA\Response(response="200", description="success", @OA\JsonContent(
     *     @OA\Property(property="id", type="integer", example="1"),
     *     @OA\Property(property="name", type="string", example="test"),
     *     @OA\Property(property="slug", type="string", example="test"),
     *     @OA\Property(property="rules", type="array", @OA\Items(ref="#/components/schemas/MediaSourceResource"))
     *   )),
     *  )
     * @param Team $team
     * @return mixed
     */
    public function index(
        Team $team
    ): mixed
    {
        return MediaSourceResource::collection(
            $team->mediaSources()->get()
        );
    }

    /**
     * @param StoreMediaToTeamRequest $request
     * @param Team                    $team
     * @return JsonResponse
     */
    /**
     * @OA\Post(
     *     tags={"Team Acl Controller & Media Sources"},
     *     path="/api/v1/mgr/acl/teams/{team}/media-sources",
     *     summary="Assign Team to Media sources",
     *     security={{ "Bearer":{} }},
     *   @OA\RequestBody(
     *      required=true,
     *      description="insert media sources",
     *      @OA\JsonContent(ref="#/components/schemas/StoreMediaToTeamRequest"),
     * ),
     *
     *     @OA\Response(
     *     response="200", description="success",
     *     @OA\JsonContent(
     *          @OA\Property(property="name", type="string", example="Media source has been added to team successfully"),
     *          @OA\Property(property="status", type="string", example="200")
     *      )),
     *     @OA\Response(response=401, description="Unauthorized"),
     *     @OA\Response(response=400, description="BAD REQUEST", @OA\JsonContent(
     *          @OA\Property(property="message", type="string", example="We couldn't be able to attach those permissions.")
     *     ))
     * )
     *
     */
    public function store(
        StoreMediaToTeamRequest $request,
        Team                    $team
    )
    {
        if ($team->mediaSources()->syncWithoutDetaching(...array_values($request->validated()))) {
            return response()->json([
                'message' => __('Media source has been added to team successfully'),
                'status' => Response::HTTP_OK
            ], Response::HTTP_OK);
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
     * @param Team        $team
     * @param MediaSource $mediaSource
     * @return JsonResponse
     */
    /**
     * @OA\delete(
     *     tags={"Team Acl Controller & Media Sources"},
     *     path="/api/v1/mgr/acl/teams/{team}/media-sources/{media_source}",
     *     summary="Delete Team Acl List",
     *     security={{ "Bearer":{} }},
     *
     *     @OA\Parameter (name="teams",in="path",required=true ),
     *     @OA\Parameter (name="media_source",in="path",required=true ),
     *
     *     @OA\Response(
     *     response="200", description="success",
     *     @OA\JsonContent(
     *          @OA\Property(property="name", type="string", example="edia source has been removed from team successfully"),
     *          @OA\Property(property="status", type="string", example="200")
     *      )),
     *     @OA\Response(response=401, description="Unauthorized"),
     *     @OA\Response(response=400, description="BAD REQUEST", @OA\JsonContent(
     *          @OA\Property(property="message", type="string", example="We couldn't be able to attach those permissions.")
     *     ))
     * )
     *
     */
    public function destroy(
        Team        $team,
        MediaSource $mediaSource
    )
    {
        if ($team->mediaSources()->detach($mediaSource->id)) {
            return response()->json([
                'message' => __('Media source has been removed from team successfully'),
                'status' => Response::HTTP_OK
            ], Response::HTTP_OK);
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
}
