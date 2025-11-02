<?php

namespace App\Http\Controllers\Tenant\Mgr\Custom\Products;

use App\Events\Tenant\Custom\CreateProductCombinationWithOutStockEvent;
use App\Events\Tenant\Custom\CreateProductCombinationWithStockEvent;
use App\Events\Tenant\Custom\CreateProductEvent;
use App\Events\Tenant\Custom\CreateProductVariationWithOutStockEvent;
use App\Events\Tenant\Custom\CreateProductVariationWithStockEvent;
use App\Events\Tenant\Custom\CreateProductWithOutStockEvent;
use App\Events\Tenant\Custom\CreateProductWithStockEvent;
use App\Events\Tenant\Custom\UpdatedProductEvent;
use App\Events\Tenant\Custom\UpdateProductExcludesUpdated;
use App\Events\Tenant\Custom\UpdateProductSkuUpdated;
use App\Http\Controllers\Controller;
use App\Http\Requests\Products\ProductCopyRequest;
use App\Http\Requests\Products\ProductsPackageRequest;
use App\Http\Requests\Products\ProductStoreRequest;
use App\Http\Requests\Products\ProductUpdateRequest;
use App\Http\Resources\Products\ProductIndexResource;
use App\Http\Resources\Products\ProductResource;
use App\Models\Tenants\Blueprint;
use App\Models\Tenants\Category;
use App\Models\Tenants\Product;
use App\Scoping\Scopes\Categories\CategoryScope;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Mtownsend\XmlToArray\XmlToArray;
use Symfony\Component\HttpFoundation\Response;


class ProductController extends Controller
{

    /**
     * @return AnonymousResourceCollection
     * @OA\Get (
     *     tags={"Custom_Products"},
     *     path="/api/v1/mgr/custom/products",
     *     summary="Get All products",
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
     *          @OA\Property(type="array", property="data", @OA\Items(ref="#/components/schemas/ProductIndexResource")),
     * @OA\Property(type="string", title="message", description="message", property="message", example=""),
     * @OA\Property(type="string", title="status", description="status", property="status", example="200"),
     *      )),
     *     @OA\Response(response=401, description="Unauthorized"),
     * )
     *
     */
    public function index(): AnonymousResourceCollection
    {
        return ProductIndexResource::collection(
            Product::withScopes($this->scopes())
                ->where('iso', app()->getLocale())
                ->paginate(request()->per_page ?? 10)
        )->additional([
            'message' => null,
            'status' => Response::HTTP_OK,
            'workflow' => Blueprint::where('ns', 'workflow_shop')->first()?->configuration
        ]);
    }

    /**
     * @param Product $product
     * @return ProductResource
     */
    public function show(
        Product $product
    )
    {
        $product->load(
            'variations', 'variations.box', 'variations.option', 'variations.product', 'skus', 'skus.media'
        );
        return new ProductResource(
            $product
        );
    }

    /**
     * @param ProductStoreRequest $request
     * @return ProductResource
     * @OA\Post (
     *     tags={"Custom_Products"},
     *     path="/api/v1/mgr/custom/products",
     *     summary="Create products",
     *     security={{ "Bearer":{} }},
     *   @OA\RequestBody(
     *      required=true,
     *      description="products setting data",
     *      @OA\JsonContent(ref="#/components/schemas/ProductStoreRequest"),
     *   ),
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
     *          @OA\Property(type="array", property="data", @OA\Items(ref="#/components/schemas/ProductResource")),
     * @OA\Property(type="string", title="message", description="message", property="message", example="product has been created successfully."),
     * @OA\Property(type="string", title="status", description="status", property="status", example="201"),
     *      )),
     *     @OA\Response(response=401, description="Unauthorized"),
     * )
     *
     */
    public function store(
        ProductStoreRequest $request
    )
    {
        $data = collect($request->validated());
        $product = Product::create($data->except(['translation'])->toArray());
        event(new CreateProductEvent($product, $data->only(['translation'])->first()));
        // create product
        if ($request->media) {
            $product->addMedia($request->media);
        }
        match ((bool)$request->get('variation')) {
            false => match ((bool)$request->get('stock_product')) {
                false => event(new CreateProductWithOutStockEvent($product, $data->except(['translation'])->toArray())),
                true => event(new CreateProductWithStockEvent($product, $data->except(['translation'])->toArray())),
            },
            true => match ((bool)$request->get('excludes')) {
                (bool)$request->get('stock_product') && (bool)!$request->get('excludes') =>
                event(new CreateProductVariationWithOutStockEvent($product, $data->except(['translation'])->toArray())),
                (bool)!$request->get('stock_product') && (bool)!$request->get('excludes') =>
                event(new CreateProductVariationWithStockEvent($product, $data->except(['translation'])->toArray())),
                (bool)!$request->get('stock_product') && (bool)$request->get('excludes') =>
                event(new CreateProductCombinationWithOutStockEvent($product, $data->except(['translation'])->toArray())),
                (bool)$request->get('stock_product') && (bool)$request->get('excludes') =>
                event(new  CreateProductCombinationWithStockEvent($product, $data->except(['translation'])->toArray())),
            },
        };

        return ProductResource::make($product)
            ->additional([
                'message' => __('Product has been created successfully.'),
                'status' => Response::HTTP_CREATED
            ]);
    }

    /**
     * @param ProductUpdateRequest $request
     * @param Product              $product
     * @return ProductResource|JsonResponse
     * @OA\Put (
     *     tags={"Custom_Products"},
     *     path="/api/v1/mgr/custom/products/{id}",
     *     summary="Update product",
     *     security={{ "Bearer":{} }},
     *   @OA\RequestBody(
     *      required=true,
     *      description="products data",
     *      @OA\JsonContent(ref="#/components/schemas/ProductUpdateRequest"),
     *   ),
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
     *          @OA\Property(type="array", property="data", @OA\Items(ref="#/components/schemas/ProductResource")),
     * @OA\Property(type="string", title="message", description="message", property="message", example="Product has been updated successfully."),
     * @OA\Property(type="string", title="status", description="status", property="status", example="201"),
     *      )),
     *     @OA\Response(response=401, description="Unauthorized"),
     * )
     *
     */
    public function update(
        Product              $product,
        ProductUpdateRequest $request,
    )
    {
        $data = $request->safe();


        $product->fill($data->except(['products', 'hasBlueprint']));
        $changedValue = $product->getDirty();
        unset($product->hasBlueprint);
        if ($product->save()) {
            if ($request->media) {
                $product->dropAndBuilt($request->media);
            }

            if ($product->variation) {
                event(new CreateProductVariationWithOutStockEvent($product, $data->except(['translation'])));
            }

            if ($product->combination) {

                app(ProductsPackageController::class)->update($product, new ProductsPackageRequest($data->toArray()));
            }
            event(new UpdatedProductEvent($product, $request->except(['translation', 'hasBlueprint'])));
            if (!$product->excludes && !$product->stock_product) {
                event(new UpdateProductSkuUpdated($product, $request->validated()));
            }

            if (in_array('stock_product', array_keys($changedValue))
                && !$changedValue['stock_product']
            ) {

                $product->skus()->get()->map(fn($sku) => $sku->stocks()->delete());
            }

            if (
                in_array('excludes', array_keys($changedValue))
                && !$changedValue['excludes']
            ) {
                event(new UpdateProductExcludesUpdated($product, $request->validated()));
            }

            return ProductResource::make($product)
                ->additional([
                    'message' => __('Product has been updated successfully.'),
                    'status' => Response::HTTP_OK
                ]);
        }

        /**
         * error response
         */
        return response()->json([
            'message' => __('We couldn\'t update this product, please try again later.'),
            'status' => Response::HTTP_BAD_REQUEST
        ], Response::HTTP_BAD_REQUEST);
    }

    /**
     * @param Product $product
     * @return JsonResponse
     * @throws Exception
     */
    /**
     * @param Brand $brand
     * @return JsonResponse
     *
     * @return mixed
     * @OA\Delete  (
     *     tags={"Custom_Products"},
     *     path="/api/v1/mgr/custom/products/{id}",
     *     summary="Delete product",
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
    public function destroy(
        Product $product
    )
    {
        if ($product->deleteWithAllRelations()) {
            return response()->json([
                'message' => __('Product deleted successfully'),
                'status' => Response::HTTP_OK
            ], Response::HTTP_OK);
        }
        /**
         * error response
         */
        return response()->json([
            'message' => __('We couldn\'t delete this product, please try again later.'),
            'status' => Response::HTTP_BAD_REQUEST
        ], Response::HTTP_BAD_REQUEST);
    }

    /**
     * @param ProductCopyRequest $request
     * @return ProductResource
     */
    public function copy(
        ProductCopyRequest $request
    )
    {
        $product = Product::create($request->validated());
        $data = collect($request->validated());

        event(new CreateProductEvent($product, $data->only(['translation'])->first()));

        // create product
        if ($request->media) {
            $product->addMedia($request->media);
        }

        match ((bool)$data->get('variation')) {
            false => match ((bool)$data->get('stock_product')) {
                false => event(new CreateProductWithOutStockEvent($product, $data->except(['translation'])->toArray())),
                true => event(new CreateProductWithStockEvent($product, $data->except(['translation'])->toArray())),
            },
        };

        return ProductResource::make($product)
            ->additional([
                'message' => __('Product has been created successfully.'),
                'status' => Response::HTTP_CREATED
            ]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function import(
        Request $request
    )
    {
        $products = XmlToArray::convert(file_get_contents($request->file('file')));
        $categoryName = optional($products['MATERIAL'])[0] ? $products['MATERIAL'][0]['@attributes']['productGroup'] : $products['MATERIAL']['@attributes']['productGroup'];
        $category = Category::FirstOrCreate(['name' => $categoryName]);
        if (optional($products['MATERIAL'])[0]) {
            collect($products['MATERIAL'])->map(function ($product) use ($category) {
                $this->storeProdcut($category, $product);
            });
        } else {
            $this->storeProdcut($category, $products['MATERIAL']);
        }
        /**
         * congratulations response
         */
        return response()->json([
            'message' => __(count($products['MATERIAL']) . 'products imported with success.'),
            'status' => Response::HTTP_OK
        ], Response::HTTP_OK);
    }

    /**
     * @param Category $category
     * @param array    $product
     */
    public function storeProdcut(
        Category $category,
        array    $product
    )
    {
        $basics = collect(optional($product['PI_DATA']['BASICS'])['ATTRIBUTE']);
        $basics = $basics->whereIn('@attributes.name', ['PRODUCT_GROUP', 'ART_NUM', 'EAN'])->pluck('@content', '@attributes.name');
        $category->products()->UpdateOrCreate([
            'iso' => 'nl',
            'ean' => optional($basics)['EAN'],
            'art_num' => optional($basics)['ART_NUM']
        ],
            [
                'iso' => 'nl',
                'name' => optional($basics)['PRODUCT_GROUP'],
                'ean' => optional($basics)['EAN'],
                'art_num' => optional($basics)['ART_NUM'],
                'properties' => json_encode([$product])
            ]
        );
    }

    /**
     * Search for products based on the request parameters.
     *
     * @param Request $request
     * @return AnonymousResourceCollection
     */
    public function search(Request $request): AnonymousResourceCollection
    {
        $product = new Product();
        $product = $product->with('skus')
        ->where('iso', app()->getLocale());
        if($request->q) {
            $product = $product->where('name', 'iLIKE', "%{$request->q}%")->with('skus')
                ->where('iso', app()->getLocale());
        }
        return ProductResource::collection($product->get())->additional([
            'message' => __('Products retrieved successfully.'),
            'status' => Response::HTTP_OK
        ]);
    }

    /**
     * @return CategoryScope[]
     */
    public function scopes()
    {
        return [
            'category' => new CategoryScope()
        ];
    }
}

