<?php

namespace App\Http\Controllers\Boops;

use App\Models\Boop;
use App\Helpers\HelperMethods;
use App\Models\Box;
use App\Models\Category;
use App\Models\Option;
use Illuminate\Http\JsonResponse;
use App\Services\Products\ProductService;
use Illuminate\Support\Str;
use function BenTools\CartesianProduct\cartesian_product;

class BoopController extends \App\Http\Controllers\Controller
{
    /**
     * Desired DB relations
     *
     * @var array $relations
     */
    private $relations = [
        'boxes',
        'boxes.options'
    ];

    /**
     * Desired Hidden Fields
     *
     * @var array $hidden
     */
    private $hidden = [
        'assortments' => [
            'title',
            'boid',
            'sku',
            'id',
        ],
        'boxes' => [
            'boid',
            'pivot'
        ],
        'options' => [
            'boid',
            'pivot'
        ]
    ];

    /**
     * Desired visible fields
     *
     * @var array $visible
     */
    private $visible = [
        'category_id'
    ];

    /**
     * excluded options
     *
     * @var array $visible
     */
    private $excludes = [
        'boxes' => [
            'printing process',
            'delivery type',
            'quantity'
        ],
        'options' => [
            'printing colors' => '4/1 Full Color'
        ]
    ];

    /**
     * create of get BOOPS
     *
     * @param Int $category_id
     * @return JsonResponse
     */
    public function store(
        Int $category_id
    )
    {
        $category = Category::where('id', $category_id)->with(['boxes', 'boxes.options' => function($q) use ($category_id) {
            return $q->where('category_id', $category_id);
        }])->first();

        $productService = new ProductService(
            $category->boxes,
            $this->excludes
        );

        $cats = $productService->cats;

        $boops = [];

        $i=0;

        foreach($cats as $box => $options)
        {

            $boops['boops'][$i]['id'] = Box::where('title', $box)->first()->id;
            $boops['boops'][$i]['title'] = $box;
            $opt = [];
            foreach ($options as $option) {
                $opt[] = [
                    'id' => Option::where('title', $option)->first()->id,
                    'title' => $option
                ];
            }
            $boops['boops'][$i]['ops'] = $opt;
            $i++;
        }
        $data = collect($boops);

//        $data->prepend(2, 'supplier_id');
//
//        $data->prepend($category_id, 'category_id');
        $boops = Boop::firstOrCreate(
            [
                'category_id' => $category_id,
            ],
            [
                'boops' => $data,
                'boid' => null
            ]
        )->toArray();

        return response()->json([
            'status' => 'success',
            'data' => $boops
        ], 200);
    }

    /**
     * Show BOOPS
     *
     * @param Int $category_id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(
        Int $category_id
    ): JsonResponse
    {
        $boops = Boop::where('category_id', $category_id)->firstOrFail();

        return response()->json([
            'status' => 'success',
            'data' => $boops->boops
        ], 200);
    }

    /**
     * @return string
     * store boops to boops service
     */
    public function storeBoopService()
    {
        $boops = Boop::all();

        /**
         * loop boops
         */
        foreach ($boops as $boop) {
            /**
             * create boops object
             */
            $boopData = [
                'category_id' => Category::find($boop->category_id)->boid,
                'supplier_id' => 2,
                'name' => Category::find($boop->category_id)->name
            ];

            /**
             * loop boxes
             */
            foreach ($boop->boops->boops as $boxKey => $box) {
                $boopData['boops'][$boxKey]['id'] = Box::find($box->id)->boid;
                $boopData['boops'][$boxKey]['type'] = 'input';
                $boopData['boops'][$boxKey]['inputType'] = 'select';
                $boopData['boops'][$boxKey]['title'] = Box::find($box->id)->title;
                $boopData['boops'][$boxKey]['value'] = Str::slug(Box::find($box->id)->title, '-');

                /**
                 * loop options
                 */
                foreach ($box->ops as $optionKey => $option) {
                    $boopData['boops'][$boxKey]['ops'][$optionKey]['id'] = Option::find($option->id)->boid;
                    $boopData['boops'][$boxKey]['ops'][$optionKey]['title'] = $option->title;
                    if(isset($option->excludes)) {
                        foreach ($option->excludes as $exclude){
                            $boopData['boops'][$boxKey]['ops'][$optionKey]['excludes'][] = Option::find($exclude)->boid;
                        }
                    }else{
                        $boopData['boops'][$boxKey]['ops'][$optionKey]['excludes'] = [];
                    }
                }
            }

            /**
             * update boops service
             */
            $guzzle = new \GuzzleHttp\Client();
            $response = $guzzle->request(
                'POST',
                config('dwd.boops_service') . '/boops',
                [
                    'timeout' => 1000,
                    'connect_timeout' => 1000,
                    'json' => $boopData
                ]
            );
            $result = $response->getBody()->getContents();

            /**
             * update boop data
             */
            Boop::find($boop->id)->update([
                'boid' => json_decode($result)->data->_id
            ]);
        }

        return "boops updated successfully";
    }
}
