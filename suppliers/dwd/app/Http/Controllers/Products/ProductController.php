<?php

namespace App\Http\Controllers\Products;

use App\Models\Category;
use App\Models\Product;

class ProductController extends \App\Http\Controllers\Controller
{
    /**
     * Gets & Store Products of Specific Category
     */
    public function store()
    {
        $products = Product::all();

        /**
         * loop products
         */
        foreach ($products as $product) {
            $category_boid = Category::where('id', $product->category_id)->first()->boid;
            $prices = [];
            foreach ($product->prices()->get() as $price) {
                $priceCollect = collect($price->price)->toArray();
                $qty = array_keys(collect(collect($price->price)->toArray()[0])->toArray());
                $dlvTitle = array_keys(collect(collect($price->price)->toArray()[0]->{$qty[0]})->toArray());
                $p = collect(collect($price->price)->toArray()[0]->{$qty[0]})->toArray()[$dlvTitle[0]];
                if(ucfirst($dlvTitle[0]) === 'Normal') {
                    $dlvDays = '5';
                }elseif (ucfirst($dlvTitle[0]) === 'Express') {
                    $dlvDays = '4';
                }elseif (ucfirst($dlvTitle[0]) === 'Overnight') {
                    $dlvDays = '2';
                }

                $prices[] = [
                    'supplier_id' => 2,
                    'tables' => [
                        'pm'  => $price->printing,
                        'qty' => $qty[0],
                        'dlv' => [
                            'title' => ucfirst($dlvTitle[0]),
                            'days' => $dlvDays
                        ],
                        'p' => $p,
                        'ppp' => $p/$qty[0]
                    ]
                ];
//                return $prices;
            }
            $productData = [
                'category_id' => $category_boid,
                'object' => $product->product,
                'prices' => $prices
            ];

            /**
             * create products
             */
            $guzzle = new \GuzzleHttp\Client();
            $response = $guzzle->request(
                'POST',
                config('dwd.price_service') . '/products',
                [
                    'timeout' => 1000000,
                    'connect_timeout' => 10000000,
                    'json' => $productData
                ]
            );
            $result = json_decode($response->getBody()->getContents(), true);
            $product->update(['boid' => $result['data']['_id']]);
        }

        return "Products updated successfully";
    }
}
