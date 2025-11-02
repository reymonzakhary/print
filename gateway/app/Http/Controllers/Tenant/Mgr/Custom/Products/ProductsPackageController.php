<?php

namespace App\Http\Controllers\Tenant\Mgr\Custom\Products;

use App\Http\Controllers\Controller;
use App\Http\Requests\Products\ProductsPackageRequest;
use App\Http\Resources\Products\ProductResource;
use App\Models\Tenants\Product;
use App\Models\Tenants\Sku;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Symfony\Component\HttpFoundation\Response;

class ProductsPackageController extends Controller
{

    /**
     * @return AnonymousResourceCollection
     * @OA\Get (
     *     tags={"Custom_Products"},
     *     path="/api/v1/mgr/custom/products/{product}/package",
     *     summary="Get All Packages",
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
     *          @OA\Property(type="array", property="data", @OA\Items(ref="#/components/schemas/ProductPackageResource")),
     * @OA\Property(type="string", title="message", description="message", property="message", example=""),
     * @OA\Property(type="string", title="status", description="status", property="status", example="200"),
     *      )),
     *     @OA\Response(response=401, description="Unauthorized"),
     * )
     *
     */
    public function index(Product $product)
    {
        if (!$product->combination) {
            return response()->json([
                'data' => [],
                'message' => __('This product is not a package'),
                'status' => Response::HTTP_OK
            ], Response::HTTP_OK);
        }

        return ProductResource::make($product)->additional([
            'message' => null,
            'status' => Response::HTTP_OK
        ]);
    }

    /**
     * @param ProductUpdateRequest $request
     * @return JsonResponse
     *
     * @OA\Post (
     *     tags={"Custom_Products"},
     *     path="/api/v1/mgr/custom/products/{product}/package",
     *     summary="Update Package",
     *     security={{ "Bearer":{} }},
     *   @OA\RequestBody(
     *      required=true,
     *      description="products setting data",
     *      @OA\JsonContent(ref="#/components/schemas/ProductUpdateRequest"),
     *   ),
     *     @OA\Response(
     *     response="200", description="success",
     *     @OA\JsonContent(
     * @OA\Property(type="string", title="message", description="message", property="message", example="Product has been created successfully."),
     * @OA\Property(type="string", title="status", description="status", property="status", example="200"),
     *      )),
     *     @OA\Response(response=401, description="Unauthorized"),
     * )
     *
     */
    public function update(Product $product, ProductsPackageRequest $request)
    {// get product id
        if ($request->products) {
            $skus_ids = collect($request->products)->pluck('sku_id')->toArray();
            $product_ids = Sku::whereIn('id', $skus_ids)->get()->map(function ($i) use ($product) {
                if ($i) {
                    return [
                        'product_id' => $i->product_id,
                        'parent_id' => $product->sku->id
                    ];
                }
            });

            // get sku_id
            $skus = $product->sku->children;
            $skus->whereNotIn('product_id', $product_ids->pluck('product_id')->toArray())->map(fn($i) => $i->delete());
            $inDatabase = $skus->whereIn('product_id', $product_ids->pluck('product_id')->toArray())->pluck('product_id');
            $inserted = $product_ids->filter(fn($i) => !in_array($i['product_id'], $inDatabase->toArray()));

            if ($inserted->count()) {
                $product->sku->children()->insert($inserted->toArray());
            }

        }
        return response()->json([
            'message' => __('Product has been added successfully.'),
            'status' => Response::HTTP_OK
        ], Response::HTTP_OK);
    }

    /**
     * @param Product $product
     * @return JsonResponse
     *
     * @return mixed
     * @OA\Delete  (
     *     tags={"Custom_Products"},
     *     path="/api/v1/mgr/custom/products/package/{sku}",
     *     summary="Delete Package",
     *     security={{ "Bearer":{} }},
     *     @OA\Response(
     *     response="200", description="1-Single with stock {<br>combination:false  ,<br> excludes:false  ,<br> variations:false ,<br>stock_product:true <br> }
     *  <br> 2-Single without Stock {<br>combination:false  ,<br> excludes:false  ,<br> variations:false ,<br>stock_product:false <br>}
     *  <br> 3-Single with stock + variations {<br>combination:false  ,<br> excludes:false  ,<br> variations:true ,<br>stock_product:true <br>}
     *  <br> 4-Single without stock + variations {<br>combination:false  ,<br> excludes:false  ,<br> variations:true ,<br>stock_product:false <br>}
     *  <br> 5-Product combinations with stock {<br>combination:false  ,<br> excludes:true  ,<br> variations:true ,<br>stock_product:true <br>}
     *  <br> 6-Product combinations without stock {<br>combination:false  ,<br> excludes:true  ,<br> variations:true ,<br>stock_product:false <br>}
     *  <br> 7-Product package with stock {<br>combination:true  ,<br> excludes:false  ,<br> variations:false ,<br>stock_product:true <br>}
     *  <br> 8-Product package without stock {<br>combination:true  ,<br> excludes:false  ,<br> variations:false ,<br>stock_product:false <br>}",
     *     @OA\JsonContent(
     * @OA\Property(type="string", title="message", description="message", property="message", example="Product has been deleted successfully."),
     * @OA\Property(type="string", title="status", description="status", property="status", example="200"),
     *      )),
     *     @OA\Response(response=401, description="Unauthorized"),
     * )
     */
    public function destroy(Product $product, Sku $sku)
    {
        if ($sku = $product->sku->children->where('id', $sku->id)->first()) {
            if ($sku->delete()) {
                return response()->json([
                    'message' => __('Product deleted successfully'),
                    'status' => Response::HTTP_OK
                ], Response::HTTP_OK);
            }
        }

        return response()->json([
            'message' => __('We couldn\'t delete this product, please try again later.'),
            'status' => Response::HTTP_BAD_REQUEST
        ], Response::HTTP_BAD_REQUEST);
    }
}
