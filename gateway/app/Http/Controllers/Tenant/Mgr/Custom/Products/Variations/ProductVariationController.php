<?php

namespace App\Http\Controllers\Tenant\Mgr\Custom\Products\Variations;

use App\Events\Tenant\Custom\CreateProductVariationWithOutStockEvent;
use App\Events\Tenant\Custom\CreateProductVariationWithStockEvent;
use App\Http\Controllers\Controller;
use App\Http\Requests\Products\SkuStoreRequest;
use App\Http\Requests\Products\SkuUpdateRequest;
use App\Http\Resources\Products\ProductSkuResource;
use App\Models\Tenants\Product;
use App\Models\Tenants\Sku;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Symfony\Component\HttpFoundation\Response;

class ProductVariationController extends Controller
{

    /**
     * @return AnonymousResourceCollection
     * @OA\Get (
     *     tags={"Custom_Products"},
     *     path="/api/v1/mgr/custom/products/{product}/variations",
     *     summary="Get All Products with Variations",
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
     *     @OA\Response(response=401, description="Unauthorized"),
     * )
     *
     */
    public function index(
        Product $product
    )
    {
        $skus = $product->skus()
            ->with([
                'variations.ancestorsAndSelf',
                'variations.ancestorsAndSelf.option',
                'variations.ancestorsAndSelf.box',
                'variations.ancestorsAndSelf.option.children',
                'variations.ancestorsAndSelf.product',
            ])->orderBy('id', 'ASC')
            ->paginate(request()->per_page ?? 10);

        return ProductSkuResource::collection($skus)
            ->additional([
                'message' => null,
                'status' => Response::HTTP_OK
            ]);
    }

    /**
     * @param ProductVariationUpdateRequest $request
     * @return ProductSkuResource
     *
     * @OA\Post (
     *     tags={"Custom_Products"},
     *     path="/api/v1/mgr/custom/products/{product}/variations/{sku}",
     *     summary="Update Product with Variations with SKU",
     *     security={{ "Bearer":{} }},
     *   @OA\RequestBody(
     *      required=true,
     *      description="products setting data",
     *      @OA\JsonContent(ref="#/components/schemas/ProductVariationUpdateRequest"),
     *   ),
     *     @OA\Response(
     *     response="200", description="success",
     *     @OA\JsonContent(
     * @OA\Property(type="string", title="message", description="message", property="message", example="Product updated successfully."),
     * @OA\Property(type="string", title="status", description="status", property="status", example="200"),
     *      )),
     *     @OA\Response(response=404, description="We could'nt handle this request!"),
     * )
     *
     */
    public function update(
        Product          $product,
        Sku              $sku,
        SkuUpdateRequest $request
    )
    {
        $data = collect($request->validated());
        if ($product->skus()->where('skus.id', $sku->id)->exists()) {
            if ($request->media) {
                $product->addMedia($request->media);
            }
            $sku->update($data->except(['stock', 'variation'])->toArray());
            if ($data->only(['stock'])->first()) {
                $sku->stocks()->create($data->only(['stock'])->first());
            }
            if ($data->only(['variation'])->first()) {
                $sku->variations->first()->update($data->only(['variation'])->first());
            }
            return response()->json([
                'message' => __('Product updated successfully.'),
                'status' => Response::HTTP_OK
            ], Response::HTTP_OK);
        }

        /**
         * error response
         */
        return response()->json([
            'message' => __('We could\'nt handle this request!'),
            'status' => Response::HTTP_BAD_REQUEST

        ], Response::HTTP_BAD_REQUEST);
    }

    /**
     * @param SkuStoreRequest $request
     * @return ProductSkuResource
     *
     * @OA\Post (
     *     tags={"Custom_Products"},
     *     path="/api/v1/mgr/custom/products/{product}/variations",
     *     summary="Create Product with Variations with SKU",
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
    public function store(Product $product, SkuStoreRequest $request)
    {
        match ((bool)$product->excludes && $product->variation) {
            (bool)$product->stock_product =>
            event(new CreateProductVariationWithOutStockEvent($product, $request->validated())),
            (bool)!$product->stock_product =>
            event(new CreateProductVariationWithStockEvent($product, $request->validated())),
        };
    }
}
