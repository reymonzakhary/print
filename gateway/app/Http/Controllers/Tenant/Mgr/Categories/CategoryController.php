<?php

namespace App\Http\Controllers\Tenant\Mgr\Categories;

use App\Events\Tenant\Categories\DeleteCategoryEvent;
use App\Http\Controllers\Controller;
use App\Http\Requests\Categories\Printing\StoreCategoryRequest;
use App\Http\Requests\Categories\Printing\UpdateCategoryRequest;
use App\Http\Resources\Categories\IndexPrintCategoryResource;
use App\Http\Resources\Categories\PrintBoopsResource;
use App\Http\Resources\Categories\PrintCategoryResource;
use App\Http\Resources\Categories\PrintSupplierCategoryResource;
use App\Jobs\Tenant\Categories\SharedCategoryJob;
use App\Models\Tenants\Media\FileManager;
use App\Services\Categories\BoopsService;
use App\Services\Suppliers\SupplierCategoryService;
use App\Services\Tenant\Categories\SupplierCategoryService as CategoryService;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;

class CategoryController extends Controller
{
    /**
     * ContextController constructor.
     * @param SupplierCategoryService $supplierCategoryService
     * @param BoopsService            $boopsService
     */
    public function __construct(
        public SupplierCategoryService $supplierCategoryService,
        public BoopsService            $boopsService,
    ) {}

    /**
     * @param Request $request
     * @return JsonResponse
     * @throws GuzzleException
     */
    /**
     * @OA\Get (
     *     tags={"Categories"},
     *     path="/api/v1/mgr/categories",
     *     summary="Get All Categories",
     *     security={{ "Bearer":{} }},
     *     @OA\Response(
     *     response="200", description="success",
     *     @OA\JsonContent(
     *          @OA\Property(type="array", property="data", @OA\Items(ref="#/components/schemas/PrintCategoryResource")),
     * @OA\Property(type="string", title="message", description="message", property="message", example="Category has been created successfully"),
     * @OA\Property(type="string", title="status", description="status", property="status", example="201"),
     *      )),
     *     @OA\Response(response=401, description="Unauthorized"),
     * )
     *
     */
    public function index(
        Request $request
    )
    {
        $request->merge([
            'host' => $request->getSchemeAndHttpHost(),
        ]);

        return PrintSupplierCategoryResource::collection(
            app(CategoryService::class)->obtainCategories($request->only(
                'host', 'uuid', 'per_page', 'page'
            ))
        )->additional([
            'message' => null,
            'status' => Response::HTTP_OK
        ]);
    }

    /**
     * @param string $category
     * @return PrintSupplierCategoryResource|JsonResponse
     * @throws GuzzleException
     */
    public function show(
        string $category
    )
    {
        $categoryService = app(CategoryService::class);
        $response = $categoryService->obtainCategory($category);
        if(optional($response)['status'] !== Response::HTTP_OK) {
            return response()->json([
                'message' => optional($response)['message'],
                'status' => optional($response)['status'],
            ]);
        }

        // Ensure response data exists
        if (!isset($response['data']) || !is_array($response['data'])) {
            return response()->json([
                'message' => __('Invalid category data'),
                'status' => Response::HTTP_UNPROCESSABLE_ENTITY,
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        // Loop Through Category Media And Delete It From The Category Collection If It Has Been Deleted From Disk
        if (!empty($response['data']['media']) && is_array($response['data']['media'])) {
            foreach ($response['data']['media'] as $index => $file) {
                if (!Storage::disk('assets')->exists(tenant()?->uuid .'/' . $file) ) {
                    $categoryService->removeCategoryMedia($category, $file);
                    optional(FileManager::where(['path' => dirname($file), 'name' => basename($file)])->first())->delete();
                    unset($response['data']['media'][$index]);
                }
            }
        }


        return PrintSupplierCategoryResource::make(
            $response['data']
        )
            ->additional([
                'message' => null,
                'status' => 200,
            ]);
    }

    /**
     * @OA\Get (
     *     tags={"Categories"},
     *     path="/api/v1/mgr/categories/{linked}",
     *     summary="Get Categories by Linked",
     *     security={{ "Bearer":{} }},
     *     @OA\Parameter(
     *         name="linked",
     *         in="path",
     *         description="The linked parameter for filtering",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response="200", description="success",
     *         @OA\JsonContent(
     *             @OA\Property(type="array", property="data", @OA\Items(ref="#/components/schemas/PrintSupplierCategoryResource")),
     *             @OA\Property(type="string", title="message", description="message", property="message"),
     *             @OA\Property(type="string", title="status", description="status", property="status", example="200"),
     *         )
     *     ),
     *     @OA\Response(response="401", description="Unauthorized"),
     * )
     */
    public function getByLinked(
        string $linked
    )
    {
        $response = app(CategoryService::class)->obtainMyCategoriesByLink($linked);
        if(optional($response)['status'] !== Response::HTTP_OK || !optional($response)['data']) {
            return response()->json([
               'message' => $response,
               'status' => optional($response)['status'],
            ]);
        }
        return IndexPrintCategoryResource::collection(
            $response['data']
        )->additional([
            'message' => null,
            'status' => Response::HTTP_OK
        ]);

    }
    /**
     * @param StoreCategoryRequest $request
     * @return PrintCategoryResource|JsonResponse
     * @throws GuzzleException
     */

    /**
     * @OA\Post (
     *     tags={"Categories"},
     *     path="/api/v1/mgr/categories",
     *     summary="Create Categories",
     *     security={{ "Bearer":{} }},
     *   @OA\RequestBody(
     *      required=true,
     *      description="insert setting data",
     *      @OA\JsonContent(ref="#/components/schemas/StoreCategoryRequest"),
     *   ),
     *     @OA\Response(
     *     response="200", description="success",
     *     @OA\JsonContent(
     *          @OA\Property(type="array", property="data", @OA\Items(ref="#/components/schemas/PrintCategoryResource")),
     * @OA\Property(type="string", title="message", description="message", property="message", example="Category has been created successfully"),
     * @OA\Property(type="string", title="status", description="status", property="status", example="201"),
     *      )),
     *     @OA\Response(response=401, description="Unauthorized"),
     * )
     *
     */
    public function store(
        StoreCategoryRequest $request
    )
    {
        $response = app(CategoryService::class)->storeCategory($request->validated());

        if ($response['status'] !== Response::HTTP_CREATED) {
            return response()->json([
                'message' => $response['message'],
                "status" => $response['status']
            ], $response['status']);
        }
        return optional($response)['data'] ? PrintBoopsResource::make(optional($response)['data'])->additional([
            "message" => __("Category has been created successfully"),
            "status" => Response::HTTP_CREATED,
        ]) : response()->json([
            "data" => [],
            "status" => Response::HTTP_OK
        ]);
    }

    /**
     * @param UpdateCategoryRequest $request
     * @param string                $category
     * @return PrintCategoryResource|JsonResponse
     * @throws GuzzleException
     */
    /**
     * @OA\Put (
     *     tags={"Categories"},
     *     path="/api/v1/mgr/categories/{slug}",
     *     summary="Update Categories",
     *     security={{ "Bearer":{} }},
     *     @OA\Parameter (
     *      name="slug",
     *     in="path",
     *     required=true
     * ),
     *   @OA\RequestBody(
     *      required=true,
     *      description="insert setting data",
     *      @OA\JsonContent(ref="#/components/schemas/UpdateCategoryRequest"),
     *   ),
     *     @OA\Response(
     *     response="200", description="success",
     *     @OA\JsonContent(
     *          @OA\Property(type="array", property="data", @OA\Items(ref="#/components/schemas/PrintCategoryResource")),
     * @OA\Property(type="string", title="message", description="message", property="message", example="Category has been created successfully"),
     * @OA\Property(type="string", title="status", description="status", property="status", example="201"),
     *      )),
     *     @OA\Response(response=401, description="Unauthorized"),
     * )
     *
     * @throws GuzzleException
     */
    public function update(
        UpdateCategoryRequest $request,
        string                $category
    )
    {
        try {
            $response = app(CategoryService::class)->updateCategory($request->validated(),$category);
            if ($response['status'] !== Response::HTTP_OK) {
                return response()->json([
                    'message' => $response['message'],
                    "status" => $response['status']
                ], $response['status']);
            }

            SharedCategoryJob::dispatch(domain(), $response['data'], $response['data']['shareable']);

            return PrintBoopsResource::make($response['data'])->additional([
                "message" => __("Category has been updated successfully"),
                "status" => Response::HTTP_OK,
            ]);
        } catch (\Exception $exception) {
            return response()->json([
                'message' => $exception->getMessage(),
                "status" => Response::HTTP_UNPROCESSABLE_ENTITY
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    /**
     * @OA\Delete (
     *     tags={"Categories"},
     *     path="/api/v1/mgr/categories/{category}",
     *     summary="Delete Category",
     *     security={{ "Bearer":{} }},
     *   @OA\Parameter(
     *      name="category",
     *      in="path",
     *      required=true,
     *      description="Category ID",
     *      @OA\Schema(
     *         type="string"
     *      )
     *   ),
     *     @OA\Response(
     *     response="200", description="success",
     *     @OA\JsonContent(
     * @OA\Property(type="string", title="message", description="message", property="message", example="The category {category} will be removed, you will be notified when the process is finished."),
     * @OA\Property(type="string", title="status", description="status", property="status", example="200"),
     *      )),
     *     @OA\Response(response=401, description="Unauthorized"),
     * )
     */
    public function destroy(
        string $category
    ): JsonResponse
    {
        app(CategoryService::class)->deleteCategory($category,tenant()->uuid);
        event(new DeleteCategoryEvent(tenant()->uuid, $category));
        return response()->json([
            "message" => _("The category {$category} will be removed, you will be notified when the process is finished. "),
            "status" => Response::HTTP_OK
        ], Response::HTTP_OK);
    }

    public function publishedCategory(
        Request $request,
        array $ids = []
    )
    {
        $requestData = $request->all();
        $requestData['mycategory'] = false;
//        $requestData['ids'] = implode(",",$ids);
        return PrintCategoryResource::collection(
            $this->supplierCategoryService->obtainPublishedCategories($request->tenant->uuid, $requestData)
        )->additional([
            'message' => null,
            'status' => Response::HTTP_OK
        ]);
    }
}
