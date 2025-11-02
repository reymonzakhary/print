<?php

namespace App\Http\Controllers\ProductPrices;

use App\Models\Box;
use App\Models\Category;
use App\Models\Option;
use App\Models\Product;
use App\Traits\CurlRequestTrait;
use Illuminate\Http\JsonResponse;

class ProductPriceController extends \App\Http\Controllers\Controller
{
    use CurlRequestTrait;

    /**
     * Gets & Store Prices of Specific Category
     *
     * @param Int $category_id
     * @return JsonResponse
     */
    public function store(
        int $category_id
    )
    {
//        dd(Category::where('id', $category_id)->first());
        $category = Category::where('id', $category_id)->with(['boxes', 'boxes.options' => function ($q) use ($category_id) {
            return $q->where('category_id', $category_id);
        }])->first();

        $boxes = $category->boxes()->pluck('title')->toArray();

        $combinations = $this->curlRequest($this->url . 'products/' . $category->sku . '/combinations');

        collect($combinations)->map(function($v) use ($category) {

            $product = $v['product']['attributes'];
            $prices = $v['product']['prices'];

            $delivery_key = array_search('Delivery Type', array_column($v['product']['attributes'], 'attribute'), true);
            $delivery = $v['product']['attributes'][$delivery_key];
            $printing_key = array_search('Printing Process', array_column($v['product']['attributes'], 'attribute'), true);
            $printing = $v['product']['attributes'][$printing_key];

            if (!Product::where('category_id', $category->id)->whereJsonContains('product', $product)->exists()) {
                $pr = Product::create(
                    [
                        'category_id' => $category->id,
                        'product' => json_encode($product, JSON_THROW_ON_ERROR | JSON_THROW_ON_ERROR)
                    ]
                );
            } else {
                $pr = Product::where('category_id', $category->id)
                    ->whereJsonContains('product', $product)
                    ->first();
            }
            collect($prices)->map(function ($value)  use ($printing, $pr) {
                if (!$pr->prices()->whereJsonContains('price', $value)->exists()) {
                    $pr->prices()->create(
                        [
                            'price' => json_encode($value, JSON_THROW_ON_ERROR | JSON_THROW_ON_ERROR),
                            'printing' => $printing['value']
                        ]
                    );
                }
            });
        });



//        collect($combinations)->map(function ($v) use ($category_id, $boxes) {
//            $delivery_key = array_search('Delivery Type', array_column($v['product']['attributes'], 'attribute'), true);
//            $delivery = $v['product']['attributes'][$delivery_key];
//            $printing_key = array_search('Printing Process', array_column($v['product']['attributes'], 'attribute'), true);
//            $printing = $v['product']['attributes'][$printing_key];
//            unset($v['product']['attributes'][$delivery_key], $v['product']['attributes'][$printing_key]);
//            $product = collect($v['product']['attributes'])->map(function ($v) use ($boxes) {
//                if (!in_array(strtolower($v['attribute']), $boxes)) {
//                    return [];
//                }
//                $pre[strtolower($v['attribute'])] = $v['value'];
//                return $pre;
//            })->reduce('array_merge', []);
//
//            if (!Product::where('category_id', $category_id)->whereJsonContains('product', $product)->exists()) {
//                $pr = Product::create(
//                    [
//                        'category_id' => $category_id,
//                        'product' => json_encode($product)
//                    ]
//                );
//            } else {
//                $pr = Product::where('category_id', $category_id)
//                    ->whereJsonContains('product', $product)
//                    ->first();
//            }
//            foreach ($v['product']['prices'] as $price) {
//                $prs = [
//                    'pm' => strtolower($printing['value']),
//                    'qty' => $price['quantity'],
//                    'dlv' => [
//                        'title' => strtolower($delivery['value']),
//                        'days' => $price['information']['deliveryDays']
//                    ],
//                    'p' => $price['price'],
//                    'ppp' => $price['price'] / $price['quantity']
//                ];
//                if (!$pr->prices()->whereJsonContains('price', $prs)->exists()) {
//                    $pr->prices()->create(
//                        [
//                            'price' => json_encode($prs),
//                            'printing' => $printing['value']
//                        ]
//                    );
//                }
//            }
//        });

        return response()->json([
            'status' => 'success',
            'data' => 'done'
        ], 200);
    }
}
