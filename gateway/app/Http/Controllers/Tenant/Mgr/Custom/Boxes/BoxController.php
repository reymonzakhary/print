<?php

namespace App\Http\Controllers\Tenant\Mgr\Custom\Boxes;

use App\Events\Tenant\Custom\CreateBoxEvent;
use App\Events\Tenant\Custom\DeleteBoxEvent;
use App\Events\Tenant\Custom\UpdatedBoxEvent;
use App\Http\Controllers\Controller;
use App\Http\Requests\Boxes\BoxStoreRequest;
use App\Http\Requests\Boxes\UpdateBoxRequest;
use App\Http\Resources\Boxes\BoxResource;
use App\Models\Tenants\Box;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

/**
 * @group Tenant Custom Assortment
 *
 * @subgroup Boxes
 *
 */
final class BoxController extends Controller
{
    /**
     * List Boxes
     *
     * @header Origin http://{sub_domin}.prindustry.test
     * @header Referer http://{sub_domin}.prindustry.test
     * @header Authorization Bearer token
     *
     * @response 200
     * {
     *    "data": [
     *        {
     *            "id": 5,
     *            "name": "color",
     *            "description": null,
     *            "slug": "color",
     *            "input_type": "single",
     *            "incremental": false,
     *            "select_limit": null,
     *            "option_limit": null,
     *            "sqm": false,
     *            "iso": "en",
     *            "base_id": 5,
     *            "is_parent": true,
     *            "media": [],
     *            "options": [],
     *            "created_by": 1,
     *            "created_at": "2024-05-19T13:21:39.000000Z",
     *            "updated_at": "2024-05-19T13:21:39.000000Z",
     *            "children": []
     *        }
     *    ],
     *    "message": null,
     *    "status": 200
     *}
     *
     * @return mixed
     */
    public function index(): mixed
    {
        $boxes = Box::tree()
            ->with('options', 'media', 'options.media', 'options.children')
            ->where('iso', app()->getLocale())
            ->orderBy(request()->order_by ?? 'id', request()->order_dir ?? 'asc')
            ->get()->toTree();

        return BoxResource::collection(
            $boxes
        )->additional([
            'message' => null,
            'status' => Response::HTTP_OK
        ]);
    }

    /**
     * Show Box
     *
     * @header Origin http://{sub_domin}.prindustry.test
     * @header Referer http://{sub_domin}.prindustry.test
     * @header Authorization Bearer token
     *
     * @response 200
     * {
     *    "data": {
     *        "id": 5,
     *        "name": "color",
     *        "description": null,
     *        "slug": "color",
     *        "input_type": "single",
     *        "incremental": false,
     *        "select_limit": null,
     *        "option_limit": null,
     *        "sqm": false,
     *        "iso": "en",
     *        "base_id": 5,
     *        "is_parent": true,
     *        "media": [],
     *        "options": [],
     *        "created_by": 1,
     *        "created_at": "2024-05-19T13:21:39.000000Z",
     *        "updated_at": "2024-05-19T13:21:39.000000Z",
     *        "children": []
     *    },
     *    "message": null,
     *    "status": 200
     * }
     *
     * @param Box $box
     * @return BoxResource
     */
    public function show(
        Box $box
    ): BoxResource {
        $box->load('options', 'options.media', 'options.children', 'media', 'children');

        return BoxResource::make($box)
            ->additional([
                'message' => null,
                'status' => Response::HTTP_OK
            ]);
    }

    /**
     * Store Box
     *
     * @header Origin http://{sub_domin}.prindustry.test
     * @header Referer http://{sub_domin}.prindustry.test
     * @header Authorization Bearer token
     *
     * @response 200
     * {
     *    "data": {
     *        "id": 5,
     *        "name": "color",
     *        "description": null,
     *        "slug": "color",
     *        "input_type": "single",
     *        "incremental": false,
     *        "select_limit": null,
     *        "option_limit": null,
     *        "sqm": false,
     *        "iso": "en",
     *        "base_id": 5,
     *        "is_parent": true,
     *        "media": [],
     *        "created_by": 1,
     *        "created_at": "2024-05-19T13:21:39.000000Z",
     *        "updated_at": "2024-05-19T13:21:39.000000Z",
     *        "children": []
     *    },
     *    "message": "Box has been created successfully.",
     *    "status": 201
     * }
     *
     */
    public function store(
        BoxStoreRequest $request
    ): BoxResource {
        $data = collect($request->validated());
        $box = Box::create($data->except(['translation'])->toArray());

        if ($request->media) {
            $box->addMedia($request->media);
        }

        event(new CreateBoxEvent($box, $data->only(['translation'])->first()));

        return BoxResource::make(
            $box
        )->additional([
            'message' => __('Box has been created successfully.'),
            'status' => Response::HTTP_CREATED
        ]);
    }

    /**
     * Update Box
     *
     * @header Origin http://{sub_domin}.prindustry.test
     * @header Referer http://{sub_domin}.prindustry.test
     * @header Authorization Bearer token
     *
     * @response 200
     * {
     *    "data": {
     *        "id": 5,
     *        "name": "color",
     *        "description": null,
     *        "slug": "color",
     *        "input_type": "single",
     *        "incremental": false,
     *        "select_limit": null,
     *        "option_limit": null,
     *        "sqm": false,
     *        "iso": "en",
     *        "base_id": 5,
     *        "is_parent": true,
     *        "media": [],
     *        "created_by": 1,
     *        "created_at": "2024-05-19T13:21:39.000000Z",
     *        "updated_at": "2024-05-19T13:21:39.000000Z",
     *        "children": []
     *    },
     *    "message": "Box has been updated successfully.",
     *    "status": 200
     * }
     *
     * @param UpdateBoxRequest $request
     * @param Box $box
     * @return BoxResource|JsonResponse
     */
    public function update(
        UpdateBoxRequest $request,
        Box $box
    ): JsonResponse|BoxResource {
        $data = collect($request->validated());

        if ($box->update($data->except(['translation'])->toArray())) {
            if ($request->media) {
                $box->dropAndBuilt($request->media);
            }

            event(new UpdatedBoxEvent($box, $data->only(['translation'])->first()));

            return BoxResource::make(
                Box::where('slug', Str::slug($box->name))->first()
            )->additional([
                'message' => __('Box has been updated successfully.'),
                'status' => Response::HTTP_OK
            ]);
        }

        /**
         * error response
         */
        return response()->json([
            'message' => __('We could\'nt handle this request!'),
            'status' => Response::HTTP_BAD_REQUEST

        ], Response::HTTP_BAD_REQUEST);
    }

    /**
     * Delete Box
     *
     * @header Origin http://{sub_domin}.prindustry.test
     * @header Referer http://{sub_domin}.prindustry.test
     * @header Authorization Bearer token
     *
     * @response 200
     * {
     *    "message": "Box has been deleted successfully.",
     *    "status": 200
     * }
     *
     * @response 422
     * {
     *    "message": "Box has options related to, please remove the options first.",
     *    "status": 422
     * }
     *
     * @param Box $box
     * @return JsonResponse
     */
    public function destroy(
        Box $box
    ): JsonResponse {
        if (!$box->hasOptions() && $box->detachMedia()->where('row_id', $box->row_id)->delete()) {
            event(new DeleteBoxEvent($box));

            return response()->json([
                'message' => __('Box has been deleted successfully.'),
                'status' => Response::HTTP_OK
            ]);
        }

        /**
         * error response
         */
        return response()->json([
            'message' => __('Box has options related to, please remove the options first.'),
            'status' => Response::HTTP_UNPROCESSABLE_ENTITY
        ], Response::HTTP_UNPROCESSABLE_ENTITY);
    }
}
