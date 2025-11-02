<?php

namespace Modules\Cms\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Cms\Core\Facades\Cms;

class CController extends Controller
{
    public function __invoke()
    {
        dd(Cms::cms());
    }
}
