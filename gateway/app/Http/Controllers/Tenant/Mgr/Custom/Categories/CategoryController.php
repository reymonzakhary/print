<?php

namespace App\Http\Controllers\Tenant\Mgr\Custom\Categories;

use App\Events\Tenant\Custom\CreateCategoryEvent;
use App\Events\Tenant\Custom\UpdatedCategoryEvent;
use App\Http\Controllers\Controller;
use App\Http\Requests\Categories\StoreCustomCategoryRequest;
use App\Http\Requests\Categories\UpdateCustomCategoryRequest;
use App\Http\Resources\Categories\CategoryResource;
use App\Models\Tenant\Category;
use App\Scoping\Scopes\Settings\SearchScope;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\HttpFoundation\Response;


/**
 * @group Tenant Custom Assortment
 *
 * @subgroup Categories
 */
class CategoryController extends Controller
{
    /**
     * default hiding field from response
     */
    protected array $hide;

    /**
     * default total result in one page
     */
    protected int $per_page = 10;

    /**
     * List Categories
     *
     * @header Origin http://{sub_domin}.prindustry.test
     * @header Referer http://{sub_domin}.prindustry.test
     * @header Authorization Bearer token
     *
     * @urlParam search string value to search in categories
     *
     * @response 200
     * {
	 * "data": [
	 * 	{
	 * 		"id": 2,
     * 		"name": "Printing",
     * 		"description": null,
     * 		"slug": "printing",
     * 		"iso": "en",
     * 		"sort": 1,
     * 		"base_id": 2,
     * 		"has_children": true,
     * 		"is_parent": true,
     * 		"parent_id": null,
     * 		"media": [],
     * 		"margin_value": null,
     * 		"margin_type": null,
     * 		"discount_value": null,
     * 		"discount_type": null,
     * 		"published": false,
     * 		"published_at": "2024-05-20T08:06:39.341623Z",
     * 		"published_by": 1,
     * 		"created_by": 1,
     * 		"created_at": "2024-05-20T08:06:39.000000Z",
     * 		"updated_at": "2024-05-20T08:06:39.000000Z",
     *      "children": []
	 * 	},
     * ],
     * "links":{},
     * "meta":{},
     * "status":200,
     * "message": null
     * }
     *
     * @return mixed
     * @throws NotFoundExceptionInterface
     * @throws ContainerExceptionInterface
     */
    public function index(): mixed
    {
        $ids = auth()->user()->userTeams->map(fn ($team) => $team->category()->pluck('row_id'))->flatten(1)->toArray();
        $categories = Category::query()->withScopes($this->scopes())
            ->parents()
            // ->whereIn('row_id', $ids)
            ->where('iso', app()->getLocale())
            ->with('children', 'media')
            ->orderBy(request()->order_by ?? 'id', request()->order_dir ?? 'asc')
            ->paginate(request()->get('per_page') ?? $this->per_page);
        return CategoryResource::collection(
            $categories
        )->additional([
            'message' => null,
            'status' => Response::HTTP_OK
        ]);
    }

    /**
     * Show Category
     *
     * @header Origin http://{sub_domin}.prindustry.test
     * @header Referer http://{sub_domin}.prindustry.test
     * @header Authorization Bearer token
     *
     * @response 200
     * {
     * 	"data": {
     * 		"id": 2,
     * 		"name": "Printing",
     * 		"description": null,
     * 		"slug": "printing",
     * 		"iso": "en",
     * 		"sort": 1,
     * 		"base_id": 2,
     * 		"has_children": true,
     * 		"is_parent": true,
     * 		"parent_id": null,
     * 		"media": [],
     * 		"margin_value": null,
     * 		"margin_type": null,
     * 		"discount_value": null,
     * 		"discount_type": null,
     * 		"published": true,
     * 		"published_at": "2024-05-20 08:06:39",
     * 		"published_by": 1,
     * 		"created_by": 1,
     * 		"created_at": "2024-05-20T08:06:39.000000Z",
     * 		"updated_at": "2024-05-20T08:06:39.000000Z"
     * 	},
     * 	"message": null,
     * 	"status": 200
     * }
     *
     * @param Category $category
     * @return CategoryResource
     */
    public function show(
        Category $category
    ): CategoryResource {
        return CategoryResource::make(
            $category
        )->additional([
            'message' => null,
            'status' => Response::HTTP_OK
        ]);
    }

    /**
     * Store Category
     *
     * @header Origin http://{sub_domin}.prindustry.test
     * @header Referer http://{sub_domin}.prindustry.test
     * @header Authorization Bearer token
     *
     * @response 200
     * {
     * 	"data": {
     * 		"id": 2,
     * 		"name": "Printing",
     * 		"description": null,
     * 		"slug": "printing",
     * 		"iso": "en",
     * 		"sort": 1,
     * 		"base_id": 2,
     * 		"has_children": true,
     * 		"is_parent": true,
     * 		"parent_id": null,
     * 		"media": [],
     * 		"margin_value": null,
     * 		"margin_type": null,
     * 		"discount_value": null,
     * 		"discount_type": null,
     * 		"published": false,
     * 		"published_at": "2024-05-20T08:06:39.341623Z",
     * 		"published_by": 1,
     * 		"created_by": 1,
     * 		"created_at": "2024-05-20T08:06:39.000000Z",
     * 		"updated_at": "2024-05-20T08:06:39.000000Z"
     * 	},
     * 	"message": null,
     * 	"status": 200
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
     * @param StoreCustomCategoryRequest $request
     * @return CategoryResource
     *
     */
    public function store(
        StoreCustomCategoryRequest $request
    ): CategoryResource {
        $validated = collect($request->validated());
        $category = Category::create($validated->except(['translation'])->toArray());

        if ($request->media) {
            $category->addMedia($request->media);
        }
        event(new CreateCategoryEvent($category, $validated->only(['translation'])->first()));

        return CategoryResource::make(
            $category
        )->additional([
            'message' => null,
            'status' => Response::HTTP_OK
        ]);
    }

    /**
     * Update Category
     *
     * @header Origin http://{sub_domin}.prindustry.test
     * @header Referer http://{sub_domin}.prindustry.test
     * @header Authorization Bearer token
     *
     * @response 200
     * {
     *	"data": {
     *		"id": 2,
     *		"name": "Printing2",
     *		"description": null,
     *		"slug": "printing2",
     *		"iso": "en",
     *		"sort": 1,
     *		"base_id": 2,
     *		"has_children": true,
     *		"is_parent": true,
     *		"parent_id": null,
     *		"media": [],
     *		"margin_value": null,
     *		"margin_type": null,
     *		"discount_value": null,
     *		"discount_type": null,
     *		"published": true,
     *		"published_at": "2024-05-20 08:35:20",
     *		"published_by": 1,
     *		"created_by": 1,
     *		"created_at": "2024-05-20T08:06:39.000000Z",
     *		"updated_at": "2024-05-20T08:35:20.000000Z"
     *	},
     *	"message": "Category has been updated successfully.",
     *	"status": 200
     *}
     *
     * @response 422
     * {
     * 	"message": "The published by field is required. (and 1 more error)",
     * 	"errors": {
     * 		"published_by": [
     * 			"The published by field is required."
     * 		],
     * 		"published_at": [
     * 			"The published at field is required."
     * 		]
     * 	}
     * }
     *
     * @response 400
     * {
     * 	"message": "We could'nt handle this request!",
     * 	"status": 400
     * }
     *
     * @param UpdateCustomCategoryRequest $request
     * @param Category                    $category
     * @return CategoryResource|JsonResponse
     *
     */
    public function update(
        UpdateCustomCategoryRequest $request,
        Category                    $category
    ): CategoryResource|JsonResponse {
        $validated = collect($request->validated());
        if ($category->update($validated->except(['translation'])->toArray())) {
            if ($request->media) {
                $category->dropAndBuilt($request->media);
            }
            event(new UpdatedCategoryEvent($category, $validated->only(['translation'])->first()));


            return CategoryResource::make(
                Category::where([
                    ['slug', Str::slug($category->name)],
                    ['iso', app()->getLocale()]
                ])->first()
            )->additional([
                'message' => __('Category has been updated successfully.'),
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
     * Delete Category
     *
     * @header Origin http://{sub_domin}.prindustry.test
     * @header Referer http://{sub_domin}.prindustry.test
     * @header Authorization Bearer token
     *
     * @response 200
     * {
     * 	"message": "Category has been deleted successfully.",
     * 	"status": 200
     * }
     *
     * @response 422
     * {
     * 	"message": "Category has products linked with, please remove the products first.",
     * 	"status": 422
     * }
     *
     * @param Category $category
     * @return JsonResponse
     * @throws Exception
     *
     */
    public function destroy(
        Category $category
    ): JsonResponse {
        if (!$category->hasProducts() && $category->detachMedia()->where('row_id', $category->row_id)->delete()) {
            return response()->json([
                'message' => __('Category has been deleted successfully.'),
                'status' => Response::HTTP_OK
            ]);
        }
        /**
         * error response
         */
        return response()->json([
            'message' => __('Category has products linked with, please remove the products first.'),
            'status' => Response::HTTP_UNPROCESSABLE_ENTITY
        ], Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    /**
     * @return array
     */
    public function scopes()
    {
        return [
            'search' => new SearchScope()
        ];
    }
}
