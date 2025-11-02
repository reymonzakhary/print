<?php

declare(strict_types=1);

namespace App\Http\Controllers\System\V2\Mgr\App;

use App\Http\Controllers\Controller;
use App\Http\Resources\Namespaces\NamespaceResource;
use App\Models\Npace;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

final class AppController extends Controller
{
    /**
     * Returns a list of all available apps (a.k. modules)
     *
     * @return AnonymousResourceCollection
     */
    public function __invoke(): AnonymousResourceCollection
    {
        return NamespaceResource::collection(
            Npace::with('areas')->get()
        )->additional([
            'message' => __('Apps data has been retrieved successfully'),
            'status' => Response::HTTP_OK,
        ]);
    }
}
