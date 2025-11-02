<?php

namespace App\Http\Controllers\Tenant\Mgr\Finder\Categories\Options;

use App\Http\Controllers\Controller;
use App\Models\Tenants\Option;
use App\Services\Tenant\Categories\OptionService;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class OptionController extends Controller
{
    /**
     * @var OptionService
     */
    protected OptionService $OptionService;

    /**
     * default total result in one page
     */
    protected int $per_page = 10;

    /**
     * UserController constructor.
     * @param Request $request
     * @param Option  $option
     */
    public function __construct(
        Request       $request,
        OptionService $OptionService
    )
    {
        $this->OptionService = $OptionService;

        /**
         * default number of pages
         */
        $this->per_page = $request->get('per_page') ?? $this->per_page;
    }

    /**
     * Obtain paginated option
     * @param string $category
     * @param string $box
     * @return mixed
     * @throws GuzzleException
     */
    public function index(
        string $category,
        string $box

    )
    {
        /** @var option obtain  $option */
        $proxy = $this->OptionService->obtainFinderBoxOptions($category, $box);

        /**
         * check if we have option
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
            'message' => __('options.no_options_available'),
            'status' => Response::HTTP_NOT_FOUND
        ], 404);
    }

}
