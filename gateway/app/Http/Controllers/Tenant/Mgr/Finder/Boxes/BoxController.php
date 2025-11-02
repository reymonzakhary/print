<?php

namespace App\Http\Controllers\Tenant\Mgr\Finder\Boxes;

use App\Http\Controllers\Controller;
use App\Http\Resources\Boxes\PrintBoxResource;
use App\Services\Tenant\Categories\BoxService;
use App\Services\Tenant\Finder\Boxes\BoxSearchService;
use App\Http\Resources\Boxes\FinderSearchBoxResource;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class BoxController extends Controller
{
    /**
     * @param BoxService $boxService
     */
    public function __construct(public BoxService $boxService)
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
        if(!$request->input('search')) {
            return PrintBoxResource::collection($this->boxService->obtainFinderSearchBoxes($request->all()))
                ->additional([
                    'message' => null,
                    'status' => Response::HTTP_OK
                ]);
        }

        $search = app(BoxSearchService::class)->obtainFinderSearchBoxes([
            'query' => $request->input('search'),
            'iso' => $request->input('iso'),
        ]);

        if(optional($search)['status'] === Response::HTTP_NOT_FOUND) {
            return response()->json([
                "message" => __("Detail Not Found"),
                "status" => Response::HTTP_NOT_FOUND
            ], Response::HTTP_NOT_FOUND);
        }

                return FinderSearchBoxResource::collection($search)
             ->additional([
                 'message' => null,
                 'status' => Response::HTTP_OK
             ]);
    }

}
