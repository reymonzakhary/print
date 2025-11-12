<?php

namespace App\Http\Controllers\Tenant\Mgr\Custom\Options;

use App\Events\Tenant\Custom\CreateOptionEvent;
use App\Events\Tenant\Custom\DeleteOptionEvent;
use App\Events\Tenant\Custom\UpdatedOptionEvent;
use App\Http\Controllers\Controller;
use App\Http\Requests\Options\StoreOptionRequest;
use App\Http\Requests\Options\UpdateOptionRequest;
use App\Http\Resources\Options\OptionResource;
use App\Models\Tenant\Option;
use App\Scoping\Scopes\Boxes\BoxScope;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

/**
 * @group Tenant Custom Assortment
 *
 * @subgroup Options
 */
final class OptionController extends Controller
{
    /**
     * List Options
     *
     * @header Origin http://{sub_domin}.prindustry.test
     * @header Referer http://{sub_domin}.prindustry.test
     * @header Authorization Bearer token
     *
     * @response 200
     * {
     *    "data": [
     *        {
     *            "id": 1,
     *            "name": "red",
     *            "description": null,
     *            "slug": "red",
     *            "box_id": 1,
     *            "input_type": "radio",
     *            "incremental_by": null,
     *            "min": 0,
     *            "max": 0,
     *            "width": "0.0000",
     *            "height": "0.0000",
     *            "length": "0.0000",
     *            "unit": "mm",
     *            "single": true,
     *            "upto": null,
     *            "display_price": "€ 0,00",
     *            "price": 0,
     *            "price_switch": false,
     *            "sort": 1,
     *            "secure": false,
     *            "parent_id": null,
     *            "iso": "en",
     *            "base_id": 1,
     *            "published": null,
     *            "created_by": 1,
     *            "published_by": null,
     *            "published_at": null,
     *            "children": [],
     *            "properties": {
     *                "props": null,
     *                "validations": []
     *            },
     *            "media": [],
     *            "created_at": "2024-05-19T13:55:41.000000Z",
     *            "updated_at": "2024-05-19T13:55:41.000000Z"
     *        }
     *    ],
     *    "message": null,
     *    "status": 200
     * }
     *
     * @return mixed
     */
    public function index(): mixed
    {
        $options = Option::tree()
            ->where('iso', app()->getLocale())
            ->orderBy(request()->order_by ?? 'id', request()->order_dir ?? 'asc')
            ->get()->toTree();

        return OptionResource::collection(
            $options
        )->additional([
            'message' => null,
            'status' => Response::HTTP_OK
        ]);
    }

    /**
     * Show Option
     *
     * @header Origin http://{sub_domin}.prindustry.test
     * @header Referer http://{sub_domin}.prindustry.test
     * @header Authorization Bearer token
     *
     * @response 200
     * {
     *    "data": {
     *        "id": 1,
     *        "name": "red",
     *        "description": null,
     *        "slug": "red",
     *        "box_id": 1,
     *        "input_type": "radio",
     *        "incremental_by": null,
     *        "min": 0,
     *        "max": 0,
     *        "width": "0.0000",
     *        "height": "0.0000",
     *        "length": "0.0000",
     *        "unit": "mm",
     *        "single": true,
     *        "upto": null,
     *        "display_price": "€ 0,00",
     *        "price": 0,
     *        "price_switch": false,
     *        "sort": 1,
     *        "secure": false,
     *        "parent_id": null,
     *        "iso": "en",
     *        "base_id": 1,
     *        "published": null,
     *        "created_by": 1,
     *        "published_by": null,
     *        "published_at": null,
     *        "children": [],
     *        "properties": {
     *            "props": null,
     *            "validations": []
     *        },
     *        "media": [],
     *        "created_at": "2024-05-19T13:55:41.000000Z",
     *        "updated_at": "2024-05-19T13:55:41.000000Z"
     *    },
     *    "message": null,
     *    "status": 200
     * }
     *
     * @param Option $option
     * @return OptionResource
     */
    public function show(
        Option $option
    ): OptionResource {
        return OptionResource::make(
            $option
        )->additional([
            'message' => null,
            'status' => Response::HTTP_OK
        ]);
    }

    /**
     * Store Option
     *
     * @header Origin http://{sub_domin}.prindustry.test
     * @header Referer http://{sub_domin}.prindustry.test
     * @header Authorization Bearer token
     *
     * @response 201
     * {
     *    "data": {
     *        "id": 1,
     *        "name": "red",
     *        "description": null,
     *        "slug": "red",
     *        "box_id": 1,
     *        "input_type": "radio",
     *        "incremental_by": null,
     *        "min": 0,
     *        "max": 0,
     *        "width": 0,
     *        "height": 0,
     *        "length": 0,
     *        "unit": "mm",
     *        "single": true,
     *        "upto": null,
     *        "display_price": "€ 0,00",
     *        "price": 0,
     *        "price_switch": false,
     *        "sort": 1,
     *        "secure": false,
     *        "parent_id": null,
     *        "iso": "en",
     *        "base_id": 1,
     *        "published": null,
     *        "created_by": 1,
     *        "published_by": null,
     *        "published_at": null,
     *        "children": [],
     *        "properties": {
     *            "validations": [],
     *            "props": null
     *        },
     *        "media": [],
     *        "created_at": "2024-05-19T13:55:41.000000Z",
     *        "updated_at": "2024-05-19T13:55:41.000000Z"
     *    },
     *    "message": "Option has been created successfully.",
     *    "status": 201
     * }
     *
     * @param StoreOptionRequest $request
     * @return OptionResource
     */
    public function store(
        StoreOptionRequest $request,
    ): OptionResource {
        $data = collect($request->validated());
        $option = Option::create($data->except(['translation'])->toArray());

        if ($request->media) {
            $option->addMedia($request->media);
        }

        event(new CreateOptionEvent($option, $data->only(['translation'])->first()));

        return OptionResource::make(
            $option
        )->additional([
            'message' => __('Option has been created successfully.'),
            'status' => Response::HTTP_CREATED
        ]);
    }

    /**
     * Update Option
     *
     * @header Origin http://{sub_domin}.prindustry.test
     * @header Referer http://{sub_domin}.prindustry.test
     * @header Authorization Bearer token
     *
     * @response 200
     * {
     *    "data": {
     *        "id": 1,
     *        "name": "red",
     *        "description": null,
     *        "slug": "red",
     *        "box_id": 1,
     *        "input_type": "radio",
     *        "incremental_by": null,
     *        "min": 0,
     *        "max": 0,
     *        "width": "0.0000",
     *        "height": "0.0000",
     *        "length": "0.0000",
     *        "unit": "mm",
     *        "single": true,
     *        "upto": null,
     *        "display_price": "€ 0,00",
     *        "price": 0,
     *        "price_switch": false,
     *        "sort": 1,
     *        "secure": false,
     *        "parent_id": null,
     *        "iso": "en",
     *        "base_id": 1,
     *        "published": null,
     *        "created_by": 1,
     *        "published_by": null,
     *        "published_at": null,
     *        "children": [],
     *        "properties": {
     *            "props": null,
     *            "validations": []
     *        },
     *        "media": [],
     *        "created_at": "2024-05-19T13:55:41.000000Z",
     *        "updated_at": "2024-05-19T14:08:21.000000Z"
     *    },
     *    "message": "Option has been updated successfully.",
     *    "status": 200
     * }
     *
     * @response 400
     * {
     *    "message": "We could'nt handle this request!",
     *    "status": 400
     * }
     *
     * @param UpdateOptionRequest $request
     * @param Option $option
     * @return OptionResource|JsonResponse
     *
     */
    public function update(
        UpdateOptionRequest $request,
        Option $option
    ): OptionResource|JsonResponse {
        $data = collect($request->validated());

        if ($option->update($data->except(['translation'])->toArray())) {
            if ($request->media) {
                $option->dropAndBuilt($request->media);
            }

            event(new UpdatedOptionEvent($option, $data->only(['translation'])->first()));

            return OptionResource::make(
                Option::where([['slug', Str::slug($option->name)], ['iso', app()->getLocale()]])->first()
            )->additional([
                'message' => __('Option has been updated successfully.'),
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
     * @Delete Option
     *
     * @header Origin http://{sub_domin}.prindustry.test
     * @header Referer http://{sub_domin}.prindustry.test
     * @header Authorization Bearer token
     *
     * @response 200
     * {
     *    "message": "Option has been deleted successfully.",
     *    "status": 200
     * }
     *
     * @response 400
     * {
     *    "message": "We couldn'nt find your request, please try again later.",
     *    "status": 400
     * }
     *
     * @param Option $option
     * @return JsonResponse
     */
    public function destroy(
        Option $option
    ): JsonResponse {
        if ($option->detachMedia()->where('row_id', $option->row_id)->delete()) {
            event(new DeleteOptionEvent($option));

            return response()->json([
                'message' => __('Option has been deleted successfully.'),
                'status' => Response::HTTP_OK
            ]);
        }

        /**
         * error response
         */
        return response()->json([
            'message' => __('We couldn\'nt find your request, please try again later.'),
            'status' => Response::HTTP_BAD_REQUEST
        ], Response::HTTP_BAD_REQUEST);
    }

    /**
     * @return array
     */
    public function scopes(): array
    {
        return [
            'box' => new BoxScope()
        ];
    }
}
