<?php

namespace App\Http\Controllers\System\V2\Mgr\Categories;

use App\Http\Controllers\Controller;
use App\Http\Resources\Categories\MatchedCategoryResource;
use App\Services\System\Categories\CategoryService;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Symfony\Component\HttpFoundation\Response;

final class MatchedCategoryController extends Controller
{

    /**
     * Create a new controller instance.
     *
     * @param CategoryService $categoryService
     */
    public function __construct(
        protected CategoryService $categoryService
    ){}

    /**
     * Retrieve and return matched system categories as a resource collection.
     *
     * @return AnonymousResourceCollection
     * @throws GuzzleException
     */
    public function index(): AnonymousResourceCollection
    {
        return MatchedCategoryResource::collection(
            $this->categoryService->obtainMatchedSystemCategories()
        )
            ->additional([
                'message' => null,
                'status' => Response::HTTP_OK
            ]);
    }
}
