<?php

namespace App\Http\Controllers\System\Mgr\Categories;

use App\Http\Controllers\Controller;
use App\Http\Requests\Categories\AttachCategoryRequest;
use App\Http\Requests\Categories\StoreSystemCategoryRequest;
use App\Http\Requests\Categories\UpdateSystemCategory;
use App\Services\System\Categories\CategoryService;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Inertia\Inertia;
use Inertia\Response;

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
//        $this->middleware('auth');
        $this->categoryService = $categoryService;
    }

    /**
     * @return Response
     */
    public function page()
    {
        return Inertia::render('Standardization', []);
    }

    /**
     * @return Factory|View|string
     * @throws GuzzleException
     */
    public function index(Request $request)
    {
        return $this->categoryService->obtainSystemCategories([
            'per_page' => (int)$request->input('per_page', 10),
            'page' => (int)$request->input('page', 1),
            'filter' => $request->input('filter'),
            'sort_by' => $request->input('sort_by', 'name'),
            'sort_dir' => $request->input('sort_dir', 'asc'),
        ]);
    }

    /**
     * @param string $category
     * @return string
     * @throws GuzzleException
     */
    public function show(
        string $category
    )
    {
        return $this->categoryService->obtainSystemCategory($category);
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
        return $this->categoryService->storeSystemCategory($request->validated());
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
        return $this->categoryService->deleteSystemCategory($category, ["force" => $request->get('force')]);
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
