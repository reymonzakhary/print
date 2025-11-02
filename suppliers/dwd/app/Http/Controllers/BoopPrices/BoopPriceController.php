<?php

namespace App\Http\Controllers\BoopPrices;

use App\Models\Category;
use App\Models\Option;
use App\Models\Product;
use App\Models\ProductPrice;
use App\Models\BoopPrice;
use App\Traits\CurlRequestTrait;
use Illuminate\Http\JsonResponse;

class BoopPriceController extends \App\Http\Controllers\Controller
{
    use CurlRequestTrait;

    /**
     * Create Prices table for one product
     *
     * @param Int $product_id
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(
        int $category_id
    )
    {
        $category = Category::where('id', $category_id)->first();
        $prices = [];
        $tt = 0;
        foreach ($category->products()->get() as $product) {
            $encProduct = [];
            foreach (array_values(get_object_vars($product->product)) as $val) {
                $encProduct[] = Option::where('title', $val)->first()->boid;
            }
            $encWithDash = implode("-", $encProduct);
            foreach ($product->prices()->get() as $k => $price) {
                BoopPrice::create([
                    'category_id' => $product->category_id,
                    'boops_category_id' => $category->boid,
                    'collection' => md5($encWithDash),
                    'product_id' => $product->id,
                    'supplier_id' => 2,
                    'tables' => collect($price->price)->toJson()
                ]);
            }
            $tt++;
        }

        return response()->json([
            'status' => 'success',
            'data' => $tt . ' done'
        ], 200);
    }

    public function storePriceService()
    {
        /**
         * get products
         */
        $products = Product::all();
        /**
         * loop products
         */
        foreach ($products as $product) {
            $priceData = [];
            $prices = [];
            $collection = '';
            $category_id = '';
            foreach ($product->boopPrices()->get() as $price) {
                $collection = $price->collection;
                $category_id = $price->boops_category_id;
                $prices[] = [
                    'supplier_id' => $price->supplier_id,
                    'tables' => json_decode($price->tables, true)
                ];
            }
            $priceData = [
                'category_id' => $category_id,
                'collection' => $collection,
                'object' => collect($product->product)->toArray(),
                'prices' => $prices
            ];
//return $priceData;
            $guzzle = new \GuzzleHttp\Client();
            $response = $guzzle->request(
                'POST',
                config('dwd.price_service') . '/prices',
                [
                    'timeout' => 10000000,
                    'connect_timeout' => 1000000,
                    'json' => $priceData
                ]
            );
            $result = $response->getBody()->getContents();
//            return $result;
//            $boopObject->update([
//                'boid' => json_decode($result)->_id
//            ]);

//            foreach ( as $boopObject) {
//
//                /**
//                 * update BoopPrice with encrypted collection
//                 */
//                $boop_category_boid = Category::where('id', $boopObject->category_id)->first()->boid;
//                $boopObject->update([
//                    'collection' => $afterEncrypt,
//                    'boops_category_id' => $boop_category_boid
//                ]);
//                $product = $boopObject->product()->first();
//                dd($boopObject->tables);
//                /**
//                 * update price service
//                 */
//                $priceData = [
//                    'product_id' => $product->boid,
//                    'category_id' => $boop_category_boid,
//                    'collection' => $afterEncrypt,
//                    'object' => collect($product->product)->toJson(),
////                    'prices' =>
//                ];
//
//                $guzzle = new \GuzzleHttp\Client();
//                $response = $guzzle->request(
//                    'POST',
//                    config('dwd.price_service') . '/prices',
//                    [
//                        'timeout' => 10000000,
//                        'connect_timeout' => 1000000,
//                        'json' => $priceData
//                    ]
//                );
//                $result = $response->getBody()->getContents();
//                dd($result);
//                $boopObject->update([
//                    'boid' => json_decode($result)->_id
//                ]);
//            }

        }

        return "prices updated successfully";
    }
}
