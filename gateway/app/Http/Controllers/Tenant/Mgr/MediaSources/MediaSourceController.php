<?php

namespace App\Http\Controllers\Tenant\Mgr\MediaSources;

use App\Http\Controllers\Controller;
use App\Http\Requests\MediaSources\MediaSourceStoreRequest;
use App\Http\Requests\MediaSources\MediaSourceUpdateRequest;
use App\Http\Resources\MediaSources\MediaSourceResource;
use App\Models\Tenants\Media\MediaSource;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;


/**
 * @group Tenant MediaSources
 */
class MediaSourceController extends Controller
{
    /**
     * List all the media sources
     * 
     * @header Origin http://{sub_domin}.prindustry.test
     * @header Referer http://{sub_domin}.prindustry.test
     * @header Authorization Bearer token
     * 
     * @response 200
     * {
     * 	"data": [
     * 		{
     * 			"id": 1,
     * 			"name": "software",
     * 			"slug": "software",
     * 			"rules": []
     * 		}
     * 	]
     * }
     *
     * @return MediaSourceResource|mixed
     */
    public function index()
    {
        return MediaSourceResource::collection(MediaSource::get());

    }

    /**
     * Show Media Source
     * 
     * @header Origin http://{sub_domin}.prindustry.test
     * @header Referer http://{sub_domin}.prindustry.test
     * @header Authorization Bearer token
     * 
     * @response 200
     * {
     * 	"data": {
     * 		"id": 1,
     * 		"name": "software",
     * 		"slug": "software",
     * 		"rules": []
     * 	}
     * }
     * 
     * @param MediaSource $mediaSource
     * @return MediaSourceResource
     */

    public function show(
        MediaSource $mediaSource
    )
    {
        return MediaSourceResource::make($mediaSource);
    }

    /**
     * @param MediaSourceStoreRequest $request
     * @return MediaSourceResource
     */
    /**
     * Store Media Source
     * 
     * @header Origin http://{sub_domin}.prindustry.test
     * @header Referer http://{sub_domin}.prindustry.test
     * @header Authorization Bearer token
     * 
     * @response 201 
     * {
     * 	"data": {
     * 		"id": 1,
     * 		"name": "software",
     * 		"slug": "software",
     * 		"rules": []
     * 	},
     * 	"message": "MediaSource has been Created successfully.",
     * 	"status": 201
     * }
     * 
     * @response 422 
     * {
     * 	"message": "The name field is required. (and 1 more error)",
     * 	"errors": {
     * 		"name": [
     * 			"The name field is required."
     * 		],
     * 		"ctx_id": [
     * 			"The ctx id field is required."
     * 		]
     * 	}
     * }
     */
    public function store(
        MediaSourceStoreRequest $request
    )
    {
        return MediaSourceResource::make(
            MediaSource::create($request->validated())
        )->additional([
            'message' => __("MediaSource has been Created successfully."),
            'status' => Response::HTTP_CREATED
        ], Response::HTTP_CREATED);
    }

    /**
     * @param MediaSourceUpdateRequest $request
     * @param MediaSource              $mediaSource
     * @return MediaSourceResource|JsonResponse
     */
    /**
     * Update Media source
     * 
     * @header Origin http://{sub_domin}.prindustry.test
     * @header Referer http://{sub_domin}.prindustry.test
     * @header Authorization Bearer token
     * 
     * @response 200
     * {
     * 	"data": {
     * 		"id": 1,
     * 		"name": "software",
     * 		"slug": "software",
     * 		"rules": []
     * 	},
     * 	"message": "Media source has been updated successfully.",
     * 	"status": 200
     * }
     * 
     * @response 400
     * {
     * 	"message": "Something went wrong.",
     * 	"status": 400
     * }
     */
    public function update(
        MediaSourceUpdateRequest $request,
        MediaSource              $mediaSource
    )
    {
        if ($mediaSource->update($request->validated())) {
            return MediaSourceResource::make($mediaSource)
                ->additional([
                    'message' => __("Media source has been updated successfully."),
                    'status' => Response::HTTP_OK
                ]);
        }

        return response()->json([
            'message' => __("Something went wrong."),
            'status' => Response::HTTP_BAD_REQUEST
        ], Response::HTTP_BAD_REQUEST);
    }

    /**
     * @param MediaSource $mediaSource
     * @return JsonResponse
     */
    /**
     * Delete Media source
     * 
     * @header Origin http://{sub_domin}.prindustry.test
     * @header Referer http://{sub_domin}.prindustry.test
     * @header Authorization Bearer token
     * 
     * @response 200
     * {
     * 	"message": "Media source has been deleted successfully.",
     * 	"status": 200
     * }
     * 
     * @response 400
     * {
     * 	"message": "Something went wrong.",
     * 	"status": 400
     * }
     * 
     */
    public function destroy(
        MediaSource $mediaSource
    )
    {
        if ($mediaSource->delete()) {
            return response()->json([
                'message' => __("Media source has been deleted successfully."),
                'status' => Response::HTTP_OK
            ], Response::HTTP_OK);
        }
        return response()->json([
            'message' => __("Something went wrong."),
            'status' => Response::HTTP_BAD_REQUEST
        ], Response::HTTP_BAD_REQUEST);
    }

}
