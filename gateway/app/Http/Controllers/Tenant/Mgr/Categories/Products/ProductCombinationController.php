<?php

namespace App\Http\Controllers\Tenant\Mgr\Categories\Products;

use App\Events\Tenant\Products\CreateProductCombinationEvent;
use App\Events\Tenant\Products\DeleteProductCombinationEvent;
use App\Http\Controllers\Controller;
use App\Http\Resources\Categories\PrintBoopsResource;
use App\Services\Categories\BoopsService;
use App\Services\Suppliers\SupplierCategoryService;
use Exception;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ProductCombinationController extends Controller
{

    public function __construct(
        public BoopsService            $boopsService,
        public SupplierCategoryService $SupplierCategoryService
    )
    {
    }

    public function index(string $category)
    {
        $boops = $this->boopsService->obtainCategoryBoops($category);
        $boops = collect($boops)->map(fn($bo) => is_array($bo) ? $bo:json_decode($bo, true))->toArray();
        $boops = array_merge(...$boops);
        if (($boops instanceof \GuzzleHttp\Psr7\Response)) {
            return $boops;
        }

        return response()->json(
            [
                "data" => PrintBoopsResource::make($boops),
                "status" => Response::HTTP_OK
            ]
        );

    }

    public function generate(
        string $category
    )
    {

        try {
            $boops = $this->boopsService->obtainCategoryBoops($category);
            if (!is_array($boops)) {
                return $boops;
            }

            $boops = collect($boops)->map(fn($bo) => is_array($bo) ? $bo:json_decode($bo, true))->toArray();

            $boops = array_merge(...$boops);
            event(new CreateProductCombinationEvent(
                    $category,
                    collect(PrintBoopsResource::make($boops))->toArray(),
                    tenant()->uuid,
                    request()->hostname->fqdn,
                    optional(request()->hostname)->host_id
                )
            );
            return response()->json(
                [
                    "message" => _('Product generation had been started, we will notify you later.'),
                    "status" => Response::HTTP_OK
                ]
            );
        } catch (Exception $e) {
            return $e;
        }
    }

    public function regenerate(
        string $category,
        Request $request
    )
    {
        try {
            $boops = $this->boopsService->obtainCategoryBoops($category);
            $boops = collect($boops)->map(fn($bo) => is_array($bo) ? $bo:json_decode($bo, true))->toArray();
            $boops = array_merge(...$boops);
            if (!is_array($boops)) {
                return $boops;
            }
            event(
                new DeleteProductCombinationEvent(
                    $category,
                    collect(PrintBoopsResource::make($boops))->toArray(),
                    tenant()->uuid,
                    hostname()->fqdn,
                    $request->get('hostname')?->host_id
                )
            );

            return response()->json(
                [
                    "message" => _('Product Regeneration had been started, we will notify you later.'),
                    "status" => Response::HTTP_OK
                ]
            );
        } catch (Exception $e) {
            return $e;
        }

    }
}
