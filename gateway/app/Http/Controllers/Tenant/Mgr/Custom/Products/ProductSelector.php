<?php

namespace App\Http\Controllers\Tenant\Mgr\Custom\Products;

use App\Http\Controllers\Controller;
use App\Http\Resources\Products\ProductVariationResource;
use App\Models\Tenant\Product;
use Symfony\Component\HttpFoundation\Response;

class ProductSelector extends Controller
{
    protected $initialVariation;

    public function __invoke(
        Product $product,
        ?int    $option = null
    )
    {
        if (!$option) {

            $product->load(
                'variations.option',
                'variations.box',
                'variations.product',
            );

            $variations = $product->variations->sortBy('sort')->groupBy('box.slug')->first();

            return response()->json([
                'data' => [
                    'box' => collect($variations?->first()?->box)->merge([
                        'options' => ProductVariationResource::collection($variations)
                    ])
                ],
                'message' => _(''),
                'status' => Response::HTTP_OK
            ], Response::HTTP_OK);
        }

        $product->load(
            'variations.children.box',
            'variations.children.option',
            'variations.children.product',
            'variations.option',
            'variations.box',
            'variations.product',
        );

        $variations = $product->variations->where('option_id', $option)?->first()?->children;
        if ($variations) {
            return response()->json([
                'data' => [
                    'box' => collect($variations?->first()?->box)->merge([
                        'options' => ProductVariationResource::collection($variations)
                    ])
                ],
                'message' => _(''),
                'status' => Response::HTTP_OK
            ], Response::HTTP_OK);
        }

        return response()->json([
            'message' => _('We could\'nt find any variations with the requested id'),
            'status' => Response::HTTP_NOT_FOUND
        ], Response::HTTP_NOT_FOUND);


    }
}
