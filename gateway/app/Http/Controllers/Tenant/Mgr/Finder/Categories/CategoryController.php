<?php

namespace App\Http\Controllers\Tenant\Mgr\Finder\Categories;

use App\Enums\Status;
use App\Foundation\ContractManager\Facades\ContractManager;
use App\Http\Controllers\Controller;
use App\Http\Resources\Categories\FinderMarketplaceResource;
use App\Http\Resources\Categories\FinderPrintCategoryResource;
use App\Http\Resources\Categories\FinderSearchCategoryResource;
use App\Models\Domain;
use App\Services\Tenant\Finder\Categories\CategoryService;
use App\Services\Tenant\Finder\Categories\SearchService;
use App\Services\Tenant\Finder\Marketplace\MarketplaceService;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Symfony\Component\HttpFoundation\Response;
use function Aws\filter;

class CategoryController extends Controller
{
    /**
     * @var CategoryService
     */
    protected CategoryService $categoryService;

    /**
     * CategoryController constructor.
     * @param CategoryService $categoryService
     */
    public function __construct(
        CategoryService $categoryService
    )
    {
        $this->categoryService = $categoryService;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response|string
     * @throws GuzzleException
     */
    public function index()
    {
        return $this->categoryService->obtainFinderCategories();
    }

    /**
     * Search for FinderSearchCategoryResources based on query input.
     *
     * @param Request $request The HTTP request containing the query input.
     * @return JsonResponse|AnonymousResourceCollection
     * @throws GuzzleException
     */
    public function search(
        Request $request
    )
    {

        $search = app(SearchService::class)->obtainFinderSearchCategories([
            'query' => $request->input('search'),
            'iso' => $request->input('iso'),
        ]);

        if(optional($search)['status'] === Response::HTTP_NOT_FOUND) {
            return response()->json([
                "message" => __("Detail Not Found"),
                "status" => Response::HTTP_NOT_FOUND
            ], Response::HTTP_NOT_FOUND);
        }
        return FinderSearchCategoryResource::collection(
            $search
        )->additional([
            'message' => null,
            'status' => Response::HTTP_OK
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param string $category
     * @return FinderMarketplaceResource|JsonResponse
     * @throws GuzzleException
     */
    public function show(
        string $category
    )
    {
        $proxy =  app(MarketplaceService::class)->obtainMarketplaceCategories($category);
        if(!optional($proxy)['message'] && count($proxy) > 0) {
            return FinderMarketplaceResource::make(...$proxy);
        }
        return response()->json([
            'message' => __('There are no shared categories.'),
            'status' => Response::HTTP_NOT_FOUND
        ], Response::HTTP_NOT_FOUND);
//        return FinderPrintBoopsResource::make($this->categoryService->obtainFinderCategory($category));
    }

    /**
     * Display the specified resource.
     *
     * @param string $category
     * @return string|void
     * @throws GuzzleException
     */
    public function boxes(
        string $category
    )
    {
        return $this->categoryService->obtainFinderCategoryBoxes($category);
    }

    /**
     * Display the specified resource.
     *
     * @param string $category
     * @param string $box
     * @return string|void
     */
    public function options(
        string $category,
        string $box
    )
    {
        return $this->categoryService->obtainFinderCategoryBoxOptions($category, $box);
    }

}
