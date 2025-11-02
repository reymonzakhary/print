<?php

namespace App\Http\Controllers\Tenant\Mgr\Finder\Categories\Boxes;

use App\Http\Controllers\Controller;
use App\Services\Tenant\Categories\BoxService;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class BoxController extends Controller
{

    /**
     * UserController constructor.
     * @param Request    $request
     * @param BoxService $boxService
     */
    public function __construct(
        Request           $request,
        public BoxService $boxService
    ){}

    /**
     * Display the specified resource.
     *
     * @param string $category
     * @return string|void
     * @throws GuzzleException
     */
    public function index(
        string $category
    )
    {
        $proxy = $this->boxService->obtainFinderCategoryBoxes($category);
        /**
         * check if we have boxes
         */
        if ($proxy) {
            return response()->json([
                'data' => $proxy,
                'message' => null,
                'status' => Response::HTTP_OK
            ], 200);

        }

        /**
         * error response
         */
        return response()->json([
            'message' => __('boxes.no_boxes_available'),
            'status' => Response::HTTP_NOT_FOUND
        ], 404);
    }
}
