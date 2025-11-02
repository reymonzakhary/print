<?php

namespace App\Http\Controllers\System\Mgr\Categories;

use App\Http\Controllers\Controller;
use App\Services\System\Categories\CategoryService;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

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
//        $this->middleware('auth');
        $this->categoryService = $categoryService;
    }


    /**
     * Display a listing of the resource.
     *
     * @return Response|string
     * @throws GuzzleException
     */
    public function index()
    {
        return $this->categoryService->obtainUnmatchedSystemCategories();
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
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return Response
     */
    public function destroy($id)
    {
        //
    }
}
