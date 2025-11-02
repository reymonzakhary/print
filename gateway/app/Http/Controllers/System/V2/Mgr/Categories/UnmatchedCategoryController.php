<?php

namespace App\Http\Controllers\System\V2\Mgr\Categories;

use App\Http\Controllers\Controller;
use App\Http\Resources\Categories\UnmatchedCategoryResource;
use App\Services\System\Categories\CategoryService;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Symfony\Component\HttpFoundation\Response;

class UnmatchedCategoryController extends Controller
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
     * Display a listing of the unmatched categories.
     *
     * @return JsonResource
     * @throws GuzzleException
     */
    public function index()
    {
        return UnmatchedCategoryResource::collection(
            $this->categoryService->obtainUnmatchedSystemCategories()
        )
            ->additional([
                'message' => null,
                'status' => 'success'
            ]);
    }


    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @return Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return Response
     */
    public function show($id)
    {
        //
    }


    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param int     $id
     * @return Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified category from the system.
     *
     * @param string $category
     * @return JsonResponse
     * @throws GuzzleException
     */
    public function destroy(
        string $category
    ): JsonResponse
    {
        $proxy = $this->categoryService->deleteUnmatchedSystemCategory($category);
        if(optional($proxy)['status'] === 200) {
            return response()->json([
                'message' => $proxy['message'],
                'status' => $proxy['status']
            ]);
        }

        return response()->json([
            'message' => optional($proxy)['message']??__('We couldn\'t delete category :category from the system', ['category' => $category]),
            'status' => optional($proxy)['status']??Response::HTTP_UNPROCESSABLE_ENTITY
        ], optional($proxy)['status']??Response::HTTP_UNPROCESSABLE_ENTITY);
    }

}
