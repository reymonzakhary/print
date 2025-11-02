<?php

namespace App\Http\Controllers\Tenant\Mgr\Catalogues;

use App\Http\Controllers\Controller;
use App\Http\Resources\Catalogues\SystemCatalogueResource;
use App\Services\System\Boxes\Options\OptionService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SystemCatalogueController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(
        Request $request,
        string $option
    )
    {
        return SystemCatalogueResource::collection(
            app(OptionService::class)
                ->obtainOptionsByBox(
                    $option,
                    $request->merge([
                        'tenant' => (bool)$request->get('supplier')? tenant()->uuid: null,
                        'per_page' => 10000000
                    ])->all()
                )
        )->additional([
            'message' => null,
            'status' => Response::HTTP_OK
        ]);
    }
}
