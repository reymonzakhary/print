<?php

namespace App\Http\Controllers\Tenant\Mgr\Custom\Products\Variations\Stocks;

use App\Http\Controllers\Controller;
use App\Http\Requests\CustomProducts\Variations\Stocks\StoreCustomProductVariationStocks;
use App\Http\Resources\Products\ProductSkuResource;
use App\Models\Tenant\Product;
use App\Models\Tenant\Sku;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Symfony\Component\HttpFoundation\Response;

class ProductVariationsStockController extends Controller
{
    /**
     * @return AnonymousResourceCollection
     * @OA\Get (
     *     tags={"Custom_Products"},
     *     path="/api/v1/mgr/custom/products/{product}/variations/{sku}/stocks",
     *     summary="Get Products with Variations with Stock",
     *     security={{ "Bearer":{} }},
     *     @OA\Response(
     *     response="200",
     *  description="1-Single with stock {<br>combination:false  ,<br> excludes:false  ,<br> variations:false ,<br>stock_product:true <br> }
     *  <br> 2-Single without Stock {<br>combination:false  ,<br> excludes:false  ,<br> variations:false ,<br>stock_product:false <br>}
     *  <br> 3-Single with stock + variations {<br>combination:false  ,<br> excludes:false  ,<br> variations:true ,<br>stock_product:true <br>}
     *  <br> 4-Single without stock + variations {<br>combination:false  ,<br> excludes:false  ,<br> variations:true ,<br>stock_product:false <br>}
     *  <br> 5-Product combinations with stock {<br>combination:false  ,<br> excludes:true  ,<br> variations:true ,<br>stock_product:true <br>}
     *  <br> 6-Product combinations without stock {<br>combination:false  ,<br> excludes:true  ,<br> variations:true ,<br>stock_product:false <br>}
     *  <br> 7-Product package with stock {<br>combination:true  ,<br> excludes:false  ,<br> variations:false ,<br>stock_product:true <br>}
     *  <br> 8-Product package without stock {<br>combination:true  ,<br> excludes:false  ,<br> variations:false ,<br>stock_product:false <br>}",
     *     @OA\JsonContent(
     *          @OA\Property(type="array", property="data", @OA\Items(ref="#/components/schemas/ProductSkuResource")),
     * @OA\Property(type="string", title="message", description="message", property="message", example=""),
     * @OA\Property(type="string", title="status", description="status", property="status", example="200"),
     *      )),
     *     @OA\Response(response=404, description="Don't have stocks"),
     * )
     *
     */
    public function index(Product $product, Sku $sku)
    {
        if ($sku = $product->skus()->where("id", $sku->id)->first()) {
            return ProductSkuResource::collection($sku->stock)
                ->additional([
                    'message' => null,
                    'status' => Response::HTTP_OK
                ]);
        }

        return response()->json([
            "message" => __("Don't have stocks"),
            "status" => Response::HTTP_NOT_FOUND
        ], Response::HTTP_NOT_FOUND);

    }

    /**
     * @param StoreCustomProductVariationStocks $request
     * @return ProductSkuResource
     *
     * @OA\Post (
     *     tags={"Custom_Products"},
     *     path="/api/v1/mgr/custom/products/{product}/variations/{sku}/stocks",
     *     summary="Create Products with Variations with Stock",
     *     security={{ "Bearer":{} }},
     *   @OA\RequestBody(
     *      required=true,
     *      description="products setting data",
     *      @OA\JsonContent(ref="#/components/schemas/SkuStoreRequest"),
     *   ),
     *     @OA\Response(
     *     response="200", description="success",
     *     @OA\JsonContent(
     *          @OA\Property(type="array", property="data", @OA\Items(ref="#/components/schemas/ProductSkuResource")),
     * @OA\Property(type="string", title="message", description="message", property="message", example="Brand has been created successfully."),
     * @OA\Property(type="string", title="status", description="status", property="status", example="201"),
     *      )),
     *     @OA\Response(response=401, description="Unauthorized"),
     * )
     *
     */
    public function store(
        Product                           $product,
        Sku                               $sku,
        StoreCustomProductVariationStocks $request
    )
    {
        if ($sku = $product->skus()->where("skus.id", $sku->id)->first()) {

            if ($product->variation && $product->excludes && $product->stock_product) {
                if ($sku->stocks()->create($request->validated())) {
                    return response()->json([
                        "message" => __("Stock has been created successfully"),
                        "status" => Response::HTTP_CREATED
                    ], Response::HTTP_CREATED);
                }
            }
            return response()->json([
                "message" => __("We can't handle this request. please try again later!"),
                "status" => Response::HTTP_UNPROCESSABLE_ENTITY
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
        return response()->json([
            "message" => __("We couldn't find the requested sku found!"),
            "status" => Response::HTTP_NOT_FOUND
        ], Response::HTTP_NOT_FOUND);
    }
}
