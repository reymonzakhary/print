<?php

namespace App\Http\Controllers\Categories;

use App\Models\Category;
use App\Helpers\HelperMethods;
use App\Traits\CurlRequestTrait;
use Illuminate\Http\JsonResponse;

class CategoryController extends \App\Http\Controllers\Controller
{
    use CurlRequestTrait;

    /**
     * Create Categories
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(): JsonResponse
    {
        return response()->json([
            'status' => 'success',
            'data' => Category::all()
        ], 200);
    }



    /**
     * Create Categories
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(): JsonResponse
    {
        $url = $this->url . 'products/categories';
        $categories = $this->curlRequest($url);

        if ($categories) {
            array_walk(
                $categories,
                static function ($k) {
                    Category::firstOrCreate($k);
                }
            );
        }

        return response()->json([
            'status' => 'success',
            'data' => (new HelperMethods)->getCreated('App\Models\Category')
        ], 200);
    }

    public function storeBoopService()
    {
        $categories = Category::all();
        foreach ($categories as $category) {
            $categoryData = [
                'name' => $category->name
            ];
            $guzzle = new \GuzzleHttp\Client();
            $response = $guzzle->request(
                'POST',
                config('dwd.boops_service') . '/assortments',
                [
                    'timeout' => 1000,
                    'connect_timeout' => 1000,
                    'json' => $categoryData
                ]
            );
            $result = $response->getBody()->getContents();
            Category::find($category->id)->update([
                'boid' => json_decode($result)->data->_id
            ]);
        }

        return "assortments updated successfully";
    }
}
