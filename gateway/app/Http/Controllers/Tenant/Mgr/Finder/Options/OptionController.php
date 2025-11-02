<?php

namespace App\Http\Controllers\Tenant\Mgr\Finder\Options;

use App\Http\Controllers\Controller;
use App\Http\Resources\Options\PrintOptionResource;
use App\Http\Resources\Options\FinderSearchOptionResource;
use App\Services\Tenant\Categories\OptionService;
use App\Services\Tenant\Finder\Options\OptionSearchService;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class OptionController extends Controller
{
    /**
     * @param OptionService $optionService
     */
    public function __construct(public OptionService $optionService)
    {
    }


    /**
     * @param Request $request
     * @return string
     * @throws GuzzleException
     */
    public function search(
        Request $request
    )
    {
        $search = app(OptionSearchService::class)->obtainFinderSearchOptions([
            'query' => $request->input('search'),
            'iso' => $request->input('iso'),
        ]);

        if(optional($search)['status'] === Response::HTTP_NOT_FOUND) {
            return response()->json([
                "message" => __("Detail Not Found"),
                "status" => Response::HTTP_NOT_FOUND
            ], Response::HTTP_NOT_FOUND);
        }

        return FinderSearchOptionResource::collection($search)
             ->additional([
                 'message' => null,
                 'status' => Response::HTTP_OK
             ]);
    }

}
