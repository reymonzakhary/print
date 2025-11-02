<?php

namespace App\Http\Controllers\Boxes;

use App\Models\Box;
use App\Models\Option;
use App\Models\Category;
use App\Helpers\HelperMethods;
use App\Models\Product;
use App\SortBoxCategory;
use App\Traits\CurlRequestTrait;
use Illuminate\Http\JsonResponse;

class BoxController extends \App\Http\Controllers\Controller
{
    use CurlRequestTrait;

    public $sort = [
        'boxes' => [
            'format',
            'printing colors',
            'material',
            'weight',
            'punchholes',
            'bundling'
        ]
    ];


    /**
     * Create boxes and options from category
     *
     * @param Int $category_id
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(
        Int $category_id
    )
    {

        $product =  Product::where('id', 300)->with('prices')->first();

        return $product;
        $category = Category::where('id', $category_id)->with(['boxes', 'boxes.options' => function($q) use ($category_id) {
            return $q->where('category_id', $category_id);
        }])->first();
        return response()->json([
            'status' => 'success',
            'data' =>  $category
        ], 200);
    }

    /**
     * Create boxes and options from category
     *
     * @param Int $category_id
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(
        Int $category_id
    )
    {
        $category = Category::where('id', $category_id)->first();

        $call = $this->curlRequest($this->url . 'products/' . $category->sku . '/attributes');

        dd($call);
        $call = array_filter(array_replace(array_flip(SortBoxCategory::$categories[$category_id]), $call), function ($v, $k){
            return is_array($v);
        }, ARRAY_FILTER_USE_BOTH);

        if($call)
        {
            unset($call['externals']);

            foreach($call as $box => $options)
            {
                $store_box = Box::firstOrCreate(
                    ['title' => $box]
                );

                $category->boxes()->syncWithoutDetaching($store_box);
                foreach($options as $option){
                    $store_option = Option::firstOrCreate(
                        ['title' => $option]
                    );
                    $store_box->options()->save($store_option, ['category_id' => $category->id]);
                }
            }
        }
        return response()->json([
            'status' => 'success',
            'data' =>  (new HelperMethods)->findWithRelation('App\Models\Category', $category_id , ['boxes', 'boxes.options'])
        ], 200);
    }

    /**
     * @return string
     * store box in boops service
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function storeBoxBoopService()
    {
        $boxes = Box::all();
        foreach ($boxes as $box) {
            $boxData = [
                'name' => $box->title
            ];
            $guzzle = new \GuzzleHttp\Client();
            $response = $guzzle->request(
                'POST',
                config('dwd.boops_service') . '/boxes',
                [
                    'timeout' => 1000,
                    'connect_timeout' => 1000,
                    'json' => $boxData
                ]
            );
            $result = $response->getBody()->getContents();
            Box::find($box->id)->update([
                'boid' => json_decode($result)->data->_id
            ]);
        }

        return "boxes updated successfully";
    }

    /**
     * @return string
     * store options in boops service
     */
    public function storeOptionBoopService()
    {
        $options = Option::all();
        foreach ($options as $option) {
            $optionData = [
                'name' => $option->title
            ];
            $guzzle = new \GuzzleHttp\Client();
            $response = $guzzle->request(
                'POST',
                config('dwd.boops_service') . '/options',
                [
                    'timeout' => 1000,
                    'connect_timeout' => 1000,
                    'json' => $optionData
                ]
            );
            $result = $response->getBody()->getContents();
            Option::find($option->id)->update([
                'boid' => json_decode($result)->data->_id
            ]);
        }

        return "options updated successfully";
    }
}
