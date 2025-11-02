<?php

namespace App\Http\Controllers\System\Mgr\Options;

use App\Http\Controllers\Controller;
use App\Services\System\Options\OptionService;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class UnlinkedOptionController extends Controller
{
    /**
     * @var OptionService
     */
    protected OptionService $optionService;

    /**
     * UnlinkedCategory constructor.
     * @param OptionService $optionService
     */
    public function __construct(
        OptionService $optionService
    )
    {
//        $this->middleware('auth');
        $this->optionService = $optionService;
    }

    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return string
     * @throws GuzzleException
     */
    public function index(
        Request $request
    )
    {
        return $this->optionService->obtainUnlinkedOptions([
            "page" => $request->get("page"),
            "per_page" => $request->get("per_page"),
            "filter" => $request->get("filter"),
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
