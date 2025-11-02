<?php

namespace App\Http\Controllers\System\Mgr\Apps;

use App\Http\Resources\Namespaces\NamespaceResource;
use App\Models\Npace;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AppController extends Controller
{

    /**
     * Get available namespaces and areas.
     *
     * This end point lets you list namespaces and areas
     *
     * @group Apps
     * @authenticated
     */
    public function __invoke()
    {
        return NamespaceResource::collection(
            Npace::with('areas')->get()
        );
    }
}
