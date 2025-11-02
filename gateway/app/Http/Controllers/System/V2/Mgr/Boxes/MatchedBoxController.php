<?php

namespace App\Http\Controllers\System\V2\Mgr\Boxes;

use App\Http\Controllers\Controller;
use App\Http\Resources\Boxes\SystemMatchedBoxResource;
use App\Services\System\Boxes\BoxService;

class MatchedBoxController extends Controller
{

    /**
     * BoxController constructor.
     * @param BoxService $boxService
     */
    public function __construct(
        protected BoxService $boxService
    ) {}

    public function index()
    {
        return SystemMatchedBoxResource::collection($this->boxService->matchedSystemBoxes());
    }
}
