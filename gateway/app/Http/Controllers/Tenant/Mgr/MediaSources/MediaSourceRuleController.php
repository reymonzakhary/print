<?php

namespace App\Http\Controllers\Tenant\Mgr\MediaSources;

use App\Http\Controllers\Controller;
use App\Http\Requests\MediaSources\StoreMediaSourceRuleRequest;
use App\Http\Requests\MediaSources\UpdateMediaSourceRuleRequest;
use App\Http\Resources\MediaSources\MediaSourceAclRuleResource;
use App\Models\Tenant\Media\MediaSource;
use App\Models\Tenant\Media\MediaSourceRule;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class MediaSourceRuleController extends Controller
{
    /**
     * @param StoreMediaSourceRuleRequest $request
     * @param MediaSource                 $media_source
     * @return MediaSourceAclRuleResource
     */
    /**
     * @OA\Post (
     *     tags={"Media Source Rule Controller"},
     *     path="/api/v1/mgr/media-sources/{id}/rules",
     *     summary="Create Media Source Rule Controller",
     *     security={{ "Bearer":{} }},
     *   @OA\RequestBody(
     *      required=true,
     *      description="insert media-sources role",
     *      @OA\JsonContent(ref="#/components/schemas/MediaSourceRuleStoreRequest"),
     *   ),
     *     @OA\Response(
     *     response="200", description="success",
     *     @OA\JsonContent(
     *          @OA\Property(type="array", property="data", @OA\Items(ref="#/components/schemas/MediaSourceAclRuleResource")),
     * @OA\Property(type="string", title="message", description="message", property="message", example="Media source rule has been Created successfully."),
     * @OA\Property(type="string", title="status", description="status", property="status", example="201"),
     *      )),
     *     @OA\Response(response=401, description="Unauthorized"),
     * )
     *
     */
    public function store(
        StoreMediaSourceRuleRequest $request,
        MediaSource                 $media_source
    )
    {
        return MediaSourceAclRuleResource::make(
            $media_source->rules()->create($request->validated())
        )->additional([
            'message' => __("Media source rule has been Created successfully."),
            'status' => Response::HTTP_OK
        ]);
    }

    /**
     * @param UpdateMediaSourceRuleRequest $request
     * @param MediaSource                  $media_source
     * @param MediaSourceRule              $mediaSourceRule
     * @return JsonResponse
     */
    /**
     * @OA\Put (
     *     tags={"Media Source Rule Controller"},
     *     path="/api/v1/mgr/media-sources/{id}/rules",
     *     summary="Create Media Source Rule Controller",
     *     security={{ "Bearer":{} }},
     *   @OA\RequestBody(
     *      required=true,
     *      description="insert media-sources role",
     *      @OA\JsonContent(ref="#/components/schemas/MediaSourceRuleStoreRequest"),
     *   ),
     *     @OA\Response(
     *     response="200", description="success",
     *     @OA\JsonContent(
     *          @OA\Property(type="array", property="data", @OA\Items(ref="#/components/schemas/MediaSourceAclRuleResource")),
     * @OA\Property(type="string", title="message", description="message", property="message", example="Media source rules has been updated successfully"),
     * @OA\Property(type="string", title="status", description="status", property="status", example="201"),
     *      )),
     *     @OA\Response(response=400, description="We couldn't delete the requested media source rules!"),
     *     @OA\Response(response=401, description="Unauthorized"),
     * )
     *
     */
    public function update(
        UpdateMediaSourceRuleRequest $request,
        MediaSource                  $media_source,
        MediaSourceRule              $mediaSourceRule
    )
    {
        if ($media_source->rules()
            ->where('media_source_rules.id', $mediaSourceRule->id)
            ->update($request->validated())) {
            return response()->json([
                'data' => null,
                'message' => __("Media source rules has been updated successfully"),
                'status' => Response::HTTP_OK
            ], Response::HTTP_OK);
        }
        return response()->json([
            'data' => null,
            'message' => __("We couldn't delete the requested media source rules!"),
            'status' => Response::HTTP_BAD_REQUEST
        ], Response::HTTP_BAD_REQUEST);

    }

    /**
     * @param MediaSource     $media_source
     * @param MediaSourceRule $mediaSourceRule
     * @return JsonResponse
     */
    /**
     * @OA\Delete (
     *     tags={"Media Source Rule Controller"},
     *     path="/api/v1/mgr/media-sources/{id}/rules/{rule_id}",
     *     summary="Create Media Source Rule Controller",
     *     security={{ "Bearer":{} }},
     *     @OA\Response(
     *     response="200", description="success",
     *     @OA\JsonContent(
     * @OA\Property(type="string", title="message", description="message", property="message", example="Media source rules has been deleted successfully"),
     * @OA\Property(type="string", title="status", description="status", property="status", example="201"),
     *      )),
     *     @OA\Response(response=400, description="We couldn't delete the requested media source rules!"),
     *     @OA\Response(response=401, description="Unauthorized"),
     * )
     *
     */
    public function destroy(
        MediaSource     $media_source,
        MediaSourceRule $rule
    )
    {
        if ($media_source->rules()->where('media_source_rules.id', $rule->id)->delete()) {
            return response()->json([
                'data' => null,
                'message' => __("Media source rules has been deleted successfully"),
                'status' => Response::HTTP_OK
            ], Response::HTTP_OK);
        }

        return response()->json([
            'data' => null,
            'message' => __("We couldn't delete the requested media source rules!"),
            'status' => Response::HTTP_BAD_REQUEST
        ], Response::HTTP_BAD_REQUEST);
    }
}
