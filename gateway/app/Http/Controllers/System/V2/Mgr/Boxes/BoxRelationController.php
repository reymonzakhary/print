<?php

namespace App\Http\Controllers\System\V2\Mgr\Boxes;

use App\Http\Controllers\Controller;
use App\Services\System\Boxes\BoxService;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class BoxRelationController extends Controller
{
    /**
     * @var BoxService
     */
    protected BoxService $boxService;

    /**
     * UnlinkedCategory constructor.
     * @param BoxService $boxService
     */
    public function __construct(
        BoxService $boxService
    )
    {
//        $this->middleware('auth');
        $this->boxService = $boxService;
    }

    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return string
     * @throws GuzzleException
     */
    public function index(
        Request $request,
        string  $option
    )
    {
        return $this->boxService->obtainSystemBoxRelations($option
            , [
                "page" => $request->get("page"),
                "per_page" => $request->get("per_page")
            ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        //
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
     * Show the form for editing the specified resource.
     *
     * @param int $id
     * @return Response
     */
    public function edit($id)
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
