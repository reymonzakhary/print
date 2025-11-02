<?php

namespace App\Http\Controllers\System\Mgr\Boxes;

use App\Http\Controllers\Controller;
use App\Services\System\Boxes\BoxService;

class MatchedBoxController extends Controller
{
    /**
     * @var BoxService
     */
    protected BoxService $boxService;

    /**
     * BoxController constructor.
     * @param BoxService $boxService
     */
    public function __construct(
        BoxService $boxService
    )
    {
        $this->middleware('auth');
        $this->boxService = $boxService;
    }
}
