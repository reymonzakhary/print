<?php

namespace App\Http\Controllers\System\V2\Mgr\Categories;

use App\Http\Controllers\Controller;
use App\Http\Requests\Categories\AttachCategoryRequest;
use App\Http\Requests\Categories\StoreSystemCategoryRequest;
use App\Http\Requests\Categories\UpdateSystemCategory;
use App\Http\Resources\Categories\LinkedCategoriesSuppliersResource;
use App\Http\Resources\Categories\SystemCategoryResource;
use App\Http\Resources\Categories\SystemCategoryResourceCollection;
use App\Http\Resources\Categories\SystemManifestResource;
use App\Services\System\Categories\CategoryService;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CategoryController extends Controller
{

    /**
     * @var CategoryService
     */
    protected CategoryService $categoryService;

    /**
     * Create a new controller instance.
     *
     * @param CategoryService $categoryService
     */
    public function __construct(
        CategoryService $categoryService
    )
    {
        $this->categoryService = $categoryService;
    }

    /**
     * @param Request $request
     * @return SystemCategoryResource|SystemCategoryResourceCollection
     * @throws GuzzleException
     */
    public function index(
        Request $request
    ): SystemCategoryResource|SystemCategoryResourceCollection
    {
        return SystemCategoryResource::collection($this->categoryService->obtainSystemCategories([
            'per_page' => (int)$request->input('per_page', 10),
            'page' => (int)$request->input('page', 1),
            'filter' => $request->input('filter'),
            'sort_by' => $request->input('sort_by', 'name'),
            'sort_dir' => $request->input('sort_dir', 'asc'),
        ]))->additional([
            'message' => null,
            'status' => Response::HTTP_OK
        ]);
    }


    /**
     * @param string $linked
     * @return LinkedCategoriesSuppliersResource
     */
    public function linkedCategoriesSuppliers(
        string $linked
    )
    {
        $proxy = $this->categoryService->obtainLinkedCategoriesSuppliers($linked);
        if(!optional($proxy)['data'] || (optional($proxy)['status'] && optional($proxy)['status'] !== 200)) {
            return response()->json([
                'message' => optional($proxy)['message'] ?? __('No suppliers found for the given linked ID.'),
                'status' => $proxy['status'] ,
            ], $proxy['status'] );
        }
        return LinkedCategoriesSuppliersResource::make(
            $proxy
            )->additional([
            'message' => null,
            'status' => Response::HTTP_OK
        ]);
    }

    /**
     * @param string $linked
     * @return LinkedCategoriesSuppliersResource
     */
    public function linkedCategoriesManifest(
        string $linked,
        string $supplier_id
    )
    {
        $proxy = $this->categoryService->obtainLinkedCategoriesManifest($linked, $supplier_id);
        if(!optional($proxy)['data'] || (optional($proxy)['status'] && optional($proxy)['status'] !== 200)) {
            return response()->json([
                'message' => optional($proxy)['message'] ?? __('No suppliers found for the given linked ID.'),
                'status' => $proxy['status'] ,
            ], $proxy['status'] );
        }
        return SystemManifestResource::make(
            $proxy['data']
            )->additional([
            'message' => "Manifest obtained successfully.",
            'status' => Response::HTTP_OK
        ]);
    }

    /**
     * @param string $category
     * @return SystemCategoryResource
     * @throws GuzzleException
     */
    public function show(
        string $category
    ): SystemCategoryResource
    {
        return SystemCategoryResource::make(
            optional($this->categoryService->obtainSystemCategory($category))['data']??[]
        )->additional([
            'message' => null,
            'status' => Response::HTTP_OK
        ]);
    }

    /**
     * @param StoreSystemCategoryRequest $request
     * @return mixed
     * @throws GuzzleException
     */
    public function store(
        StoreSystemCategoryRequest $request
    )
    {
        $response =  $this->categoryService->storeSystemCategory($request->validated());
        if(!isset($response['data']) || $response['status'] !== 201) {
            return response()->json([
                'message' => optional($response)['message'],
                'status' => optional($response)['status'],
            ] , optional($response)['status']);
        }
        return SystemCategoryResource::make(
            $response['data']??[]
        )->additional([
            'message' => null,
            'status' => Response::HTTP_OK
        ]);

    }

    /**
     * @param UpdateSystemCategory $request
     * @param string               $category
     * @return string
     * @throws GuzzleException
     */
    public function update(
        UpdateSystemCategory $request,
        string               $category
    )
    {
        return $this->categoryService->updateSystemCategory($category, $request->validated());
    }

    /**
     * @param Request $request
     * @param string  $category
     * @return string
     * @throws GuzzleException
     */
    public function destroy(
        Request $request,
        string  $category
    )
    {
        return $this->categoryService->deleteSystemCategory($category, ["force" => $request->input('force')]);
    }

    /**
     * @param AttachCategoryRequest $request
     * @param string                $category
     * @return string
     * @throws GuzzleException
     */
    public function attach(
        AttachCategoryRequest $request,
        string                $category
    )
    {
        return $this->categoryService->obtainAttachSystemCategories($category, $request->validated());
    }

    /**
     * @param AttachCategoryRequest $request
     * @param string                $category
     * @return string
     * @throws GuzzleException
     */
    public function detach(
        AttachCategoryRequest $request,
        string                $category
    )
    {
        return $this->categoryService->obtainDetachSystemCategories($category, $request->validated());
    }

}
