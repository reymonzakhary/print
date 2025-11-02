<?php

namespace App\Http\Controllers\Tenant\Mgr\Custom\Brands;

use App\Events\Tenant\Custom\CreateBrandEvent;
use App\Events\Tenant\Custom\UpdatedBrandEvent;
use App\Http\Controllers\Controller;
use App\Http\Requests\Brands\StoreBrandRequest;
use App\Http\Requests\Brands\UpdateBrandRequest;
use App\Http\Resources\Brands\BrandResource;
use App\Models\Tenants\Brand;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

/**
 * @group Tenant Custom Assortment 
 * 
 * @subgroup Brands
 */
class BrandController extends Controller
{

    /**
     * List Brands 
     * 
     * @header Origin http://{sub_domin}.prindustry.test
     * @header Referer http://{sub_domin}.prindustry.test
     * @header Authorization Bearer token
     * 
     * @response 200
     * {
	 * "data": [
	 * 	{
	 * 		"id": 1,
	 * 		"name": "CHD",
	 * 		"slug": "chd",
	 * 		"description": null,
	 * 		"sort": 1,
	 * 		"iso": "en",
	 * 		"published": true,
	 * 		"created_by": 1,
	 * 		"published_by": 1,
	 * 		"published_at": "2024-05-20 07:19:39",
	 * 		"media": [],
	 * 		"created_at": "2024-05-20T07:19:39.000000Z",
	 * 		"updated_at": "2024-05-20T07:19:39.000000Z"
	 * 	},
     * ],
     * "message": null,
     * "status": 200
     * }
     * 
     * @return AnonymousResourceCollection
     *
     */
    public function index(): AnonymousResourceCollection
    {
        return BrandResource::collection(
            Brand::query()->with('media')->where('iso', app()->getLocale())->get()
        )->additional([
            'message' => null,
            'status' => Response::HTTP_OK
        ]);
    }

    /**
     * Show Brand 
     * 
     * @header Origin http://{sub_domin}.prindustry.test
     * @header Referer http://{sub_domin}.prindustry.test
     * @header Authorization Bearer token
     * 
     * @response 200
     * {
     * 	"data": {
     * 		"id": 1,
     * 		"name": "CHD",
     * 		"slug": "chd",
     * 		"description": null,
     * 		"sort": 1,
     * 		"iso": "en",
     * 		"published": true,
     * 		"created_by": 1,
     * 		"published_by": 1,
     * 		"published_at": "2024-05-20 07:19:39",
     * 		"media": [],
     * 		"created_at": "2024-05-20T07:19:39.000000Z",
     * 		"updated_at": "2024-05-20T07:19:39.000000Z"
     * 	},
     * 	"message": null,
     * 	"status": 200
     * }
     * 
     * @param Brand $brand
     * @return BrandResource
     */
    public function show(
        Brand $brand
    ): BrandResource
    {
        return BrandResource::make(
            $brand
        )->additional([
            'message' => null,
            'status' => Response::HTTP_OK
        ]);
    }

    /**
     * Store Brand 
     * 
     * @header Origin http://{sub_domin}.prindustry.test
     * @header Referer http://{sub_domin}.prindustry.test
     * @header Authorization Bearer token
     * 
     * @response 201 
     * {
     * 	"data": {
     * 		"id": 13,
     * 		"name": "prindustry",
     * 		"slug": "prindustry",
     * 		"description": null,
     * 		"sort": 13,
     * 		"iso": "en",
     * 		"published": null,
     * 		"created_by": 1,
     * 		"published_by": 1,
     * 		"published_at": "2024-05-20T07:22:20.089359Z",
     * 		"media": [],
     * 		"created_at": "2024-05-20T07:22:20.000000Z",
     * 		"updated_at": "2024-05-20T07:22:20.000000Z"
     * 	},
     * 	"message": "Brand has been created successfully.",
     * 	"status": 201
     * }
     * 
     * @response 422
     * {
     * 	"message": "The name field is required.",
     * 	"errors": {
     * 		"name": [
     * 			"The name field is required."
     * 		]
     * 	}
     * }
     * 
     * @param StoreBrandRequest $request
     * @return BrandResource
     */
    public function store(
        StoreBrandRequest $request
    ): BrandResource
    {
        $data = collect($request->validated());
        $brand = Brand::create($data->except(['translation'])->toArray());

        if ($request->media) {
            $brand->addMedia($request->media);
        }
        event(new CreateBrandEvent($brand, $data->only(['translation'])->first()));
        return BrandResource::make(
            $brand
        )->additional([
            'message' => __('Brand has been created successfully.'),
            'status' => Response::HTTP_CREATED
        ]);
    }

    /**
     * Update Brand 
     * 
     * @header Origin http://{sub_domin}.prindustry.test
     * @header Referer http://{sub_domin}.prindustry.test
     * @header Authorization Bearer token
     * 
     * @response 200
     * {
     * 	"data": {
     * 		"id": 1,
     * 		"name": "lol",
     * 		"slug": "lol",
     * 		"description": null,
     * 		"sort": 1,
     * 		"iso": "en",
     * 		"published": true,
     * 		"created_by": 1,
     * 		"published_by": 1,
     * 		"published_at": "2024-05-20 07:47:50",
     * 		"media": [],
     * 		"created_at": "2024-05-20T07:19:39.000000Z",
     * 		"updated_at": "2024-05-20T07:47:50.000000Z"
     * 	},
     * 	"message": "Brand has been updated successfully.",
     * 	"status": 200
     * }
     * 
     * @response 400
     * {
     * 	"message": "We could'nt handle this request!",
     * 	"status": 400
     * }
     * 
     * @param UpdateBrandRequest $request
     * @param Brand              $brand
     * @return BrandResource|JsonResponse
     *
     */
    public function update(
        UpdateBrandRequest $request,
        Brand              $brand
    ): BrandResource|JsonResponse
    {
        $data = collect($request->validated());
        if ($brand->update($data->except(['translation'])->toArray())) {
            event(new UpdatedBrandEvent($brand, $data->only(['translation'])->first()));
            if ($request->media) {
                $brand->dropAndBuilt($request->media);
            }
            return BrandResource::make(
                Brand::where('slug', Str::slug($brand->name))->first()
            )->additional([
                'message' => __('Brand has been updated successfully.'),
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
     * Delete Brand 
     * 
     * @header Origin http://{sub_domin}.prindustry.test
     * @header Referer http://{sub_domin}.prindustry.test
     * @header Authorization Bearer token
     * 
     * @response 200
     * {
     * 	"message": "Brand has been deleted successfully.",
     * 	"status": 200
     * }
     * 
     * @response 422
     * {
     * 	"message": "Brand has categories linked with, please remove the categories first.",
     * 	"status": 422
     * }
     * 
     * @param Brand $brand
     * @return JsonResponse
     *
     */
    public function destroy(
        Brand $brand
    ): JsonResponse
    {
        if ($brand->detachMedia()->where('row_id', $brand->row_id)->delete()) {
            return response()->json([
                'message' => __('Brand has been deleted successfully.'),
                'status' => Response::HTTP_OK
            ]);
        }
        /**
         * error response
         */
        return response()->json([
            'message' => __('Brand has categories linked with, please remove the categories first.'),
            'status' => Response::HTTP_UNPROCESSABLE_ENTITY
        ], Response::HTTP_UNPROCESSABLE_ENTITY);

    }
}
