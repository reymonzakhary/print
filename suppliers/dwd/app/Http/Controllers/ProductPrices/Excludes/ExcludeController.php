<?php

namespace App\Http\Controllers\ProductPrices\Excludes;

use App\Models\Boop;
use App\Models\Exclude;
use App\Models\Product;
use App\Models\Category;
use App\Helpers\HelperMethods;
use App\SortBoxCategory;
use Illuminate\Http\JsonResponse;
use App\Services\Products\ProductService;
use function BenTools\CartesianProduct\cartesian_product;

class ExcludeController extends \App\Http\Controllers\Controller
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
            'active',
            'title',
            'boid',
            'sku',
            'id'
        ],
        'boxes' => [
            'boid',
            'slug',
            'pivot'
        ],
        'options' => [
            'boid',
            'slug',
            'pivot',
            'option',
            'name'
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
            'delivery type',
            'printing process',
            'quantity'
        ],
        'options' => [
            'printing colors' => '4/1 Full Color'
        ]
    ];

    /**
     * quantities
     *
     * @var array $visible
     */
    private $quantities = [
        'digital' => [
            25,
            50,
            75,
            100,
            150,
            200,
            250,
            300,
            350,
            400,
            450,
            500,
            600,
            700,
            750
        ],
        'offset' => [
            500,
            1000,
            2500,
            5000,
            7500,
            10000,
            15000,
            20000,
            25000,
            30000,
            35000,
            40000,
            45000,
            50000
        ]
    ];

    /**
     * Show BOOPS
     *
     * @param Int $category_id
     * @return JsonResponse
     */
    public function show(
        int $category_id
    )
    {
        $category = Category::where('id', $category_id)->with(['boxes', 'boxes.options' => function ($q) use ($category_id) {
            return $q->where('category_id', $category_id);
        }])->first();

        $productService = new ProductService(
            $category->boxes,
            $this->excludes
        );

        $cats = $productService->cats;
        $prCounter = 0;
        $loopCounter = 0;



        dd(count(cartesian_product($cats)));



        foreach (cartesian_product($cats) as $c => $comb) {
            $comb = array_filter(array_replace(array_flip(SortBoxCategory::$categories[$category_id]), $comb), function ($v, $k){
                return is_string($v);
            }, ARRAY_FILTER_USE_BOTH);

            if (!Product::where('category_id', $category->id)->whereJsonContains('product', $comb)->exists()) {
//                continue;
                $baseCombination = $comb;
                $resetCombination = $comb;
                $firstElm = array_slice($comb, 0, 1);
                array_shift($comb);
                $countArr = count($comb);
                $found = [];
                $done = false;
                $i = 1;
                while (!$done) {
                    $options = array_values($comb);
                    $boxs = array_flip($comb);

                    $baseOption = $options[$i - 1];
                    $baseBox = $boxs[$options[$i - 1]];
                    $box = $cats[$boxs[$options[$i - 1]]];

                    $index = array_search($baseOption, $box, true);
                    unset($box[$index]);
                    $box = array_values($box);
                    $baseCombination = $resetCombination;
                    $excludes = [];
                    foreach ($box as $k => $v) {
                        $baseCombination[$baseBox] = $v;
//                        dump($baseCombination, Product::where('category_id', $category->id)->whereJsonContains('product', $baseCombination)->exists());
                        if ($tty = Product::where('category_id', $category->id)->whereJsonContains('product', $baseCombination)->exists()) {
                            $found[$baseBox] = $baseOption;
                            break;
                        }

                    }

//                    if (count($found) === 1) {
//                        $found = $firstElm + $found;
//                        dd($baseCombination, $found);
//                    }

                    if ($i >= $countArr) {
                        $done = true;
                        if (count($found) === 3) {
                            dump($resetCombination, $found);
                            Exclude::create([
                                "category_id" => $category_id,
                                "exclude" => json_encode($found),
                                "combination" => json_encode($resetCombination)
                            ]);
                        }
                        if (count($found) === 1) {
                            dump($resetCombination,$found);
                            Exclude::create([
                                "category_id" => $category_id,
                                "exclude" => json_encode($found),
                                "combination" => json_encode($resetCombination)
                            ]);
//                            break;
//                            $found = $firstElm + $found;
                        }
                        if (count($found) === 2) {
//                            dump($resetCombination,$found);
//                            break;
                            $excludes = [];
                            foreach ($found as $b => $o) {
                                $box_id = (new HelperMethods)->pluckFromArray('App\Models\box', 'title', $b, 'id');
                                $option_id = (new HelperMethods)->pluckFromArray('App\Models\Option', 'title', $o, 'id');
                                $excludes[] = ['box_id' => $box_id, 'option_id' => $option_id];
                            }

                            $boops = Boop::where('category_id', $category_id)->first();
                            $objBoops = $boops->boops->boops;

//                            $catBoops = $boops->boops->category_id;
//                            $supBoops = $boops->boops->supplier_id;
                            foreach ($objBoops as $b) {
                                $countExcludes = count($excludes);
                                for ($c = 0; $c < $countExcludes; $c++) {
                                    if ($b->id === $excludes[$c]['box_id']) {
                                        $ops = collect($b->ops)->map(function ($v) use ($excludes, $c) {
                                            if ($v->id === $excludes[$c]['option_id']) {
                                                $x = $c === 0 ? $c + 1 : $c - 1;
                                                $v->excludes[] = $excludes[$x]['option_id'];

                                                if (isset($v->excludes)) {
                                                    $v->excludes = array_unique($v->excludes);
                                                }
                                                return $v;
                                            }
                                        });
                                    }
                                }
                            }
//                            $object['category_id'] = $catBoops;
                            $object['boops'] = $objBoops;
//                            $object['supplier_id'] = $supBoops;
                            $boops->update(['boops'=> collect($object)]);
                        }
                    }
                    $i++;
                }
            }
        }

        return response()->json([
            'status' => 'success',
            'data' => app('App\Http\Controllers\Boops\BoopController')->show($category_id)
        ], 200);
    }


    public function test()
    {
        //            dump('out');
        ///////
        if (Product::whereJsonContains('product', $comb)->exists()) {
            $product = Product::whereJsonContains('product', $comb)->first();
        } else {
            $fail_product = $comb;
            $prCounter++;
            if (isset($product)) {
                $differences = array_diff_assoc(
                    $fail_product,
                    (array)$product->product
                );
                $countDiff = count($differences);
                $s = 0;

                for ($i = 0; $i < $countDiff; $i++) {
//                    for($countDiff; $countDiff > 0; $countDiff--){

                    $options = array_values($differences);

                    $boxs = array_flip($differences);

                    $box_name = $boxs[$options[$i]];
                    $s = $i > 0 ? ($i - 1) : 0;
                    $boxS = $cats[$boxs[$options[$s]]];
                    $box = $cats[$boxs[$options[$i]]];

                    $option = $options[$i];
                    $optionS = $options[$s];
                    $index = array_search($options[$i], $box);

                    unset($box[$index]);

                    foreach ($box as $b) {

                        $fail_product[$boxs[$option]] = $b;

                        $request = Product::whereJsonContains('product', $fail_product)->exists();

                        if ($request) {
                            break;
                        }
                    }

                    $fail_product[$boxs[$option]] = $option;

                    if (!$request) {
                        break;
                    }
                    $s++;
                }
                $exc_id = (new HelperMethods)->pluckFromArray('App\Models\Option', 'title', $optionS, 'id');
//dd( $optionS,$option,$exc_id);
                $excludes = [];

                foreach ($comb as $key => $value) {
                    if ($optionS === $value) {
                        break;
                    }

                    $box_id = (new HelperMethods)->pluckFromArray('App\Models\box', 'title', $key, 'id');

                    $option_id = (new HelperMethods)->pluckFromArray('App\Models\Option', 'title', $value, 'id');

                    $excludes[(int)$box_id] = $option_id;
                }

                $boops = Boop::where('category_id', $category_id)->first();

                $objBoops = $boops->boops->boops;

                $catBoops = $boops->boops->category_id;

                $supBoops = $boops->boops->supplier_id;

                foreach ($objBoops as $l => $b) {
                    $ops = collect($b->ops)->map(function ($v) use ($exc_id, $box_id, $differences, $excludes, $product, $fail_product) {
                        if ($v->id === $exc_id) {
                            $v->excludes[] = json_encode($excludes);
                            if (isset($v->excludes)) {
                                $v->excludes = array_unique($v->excludes);
                            }
                            return $v;
                        }
                    });
                }
//                    dd($fail_product);
//                    dump($fail_product);
                $object['category_id'] = $catBoops;

                $object['boops'] = $objBoops;

                $object['supplier_id'] = $supBoops;

                $boops->update(['boops' => collect($object)]);
            } else {
                /**
                 * if first element return 500
                 */
                /** @todo think about it */
            }
        }
//            if($loopCounter > 100 ) {
//                break;
//            }
//            $loopCounter++;
    }

    /**
     * @param $category_id
     * @param $url
     * @param $model
     * @param $combinations
     * @param $delivery
     * @param $type
     */
    public function handel(
        $category_id,
        $url,
        $model,
        $combinations,
        $delivery,
        $type
    )
    {
        $req_combinations = (object)[];

        $result = [];

        foreach (cartesian_product($delivery) as $delivery) {
            $req_combinations->sku = $model->sku;

            $req_combinations->options = array_merge($combinations, $delivery);

            $req_combinations->shipments[]['copies'] = $delivery['copies'];

            $req_combinations->maxDesigns = $model->max_designs;

            $req_combinations->deliveryPromise = false;

            $response = $this->curlPostRequest($url, json_encode($req_combinations));

            if ($response['code'] == 200) {
                $result[]['price'] = $response['data'];
                $result[]['delivery'] = $delivery;
            }
        }

        if (count($result) > 0) {
            /**
             * check existence
             */
            if (Product::whereJsonContains('product', $combinations)->exists()) {
                $product = Product::whereJsonContains('product', $combinations)->first();
            } else {
                $product = Product::create([
                    'category_id' => $category_id,
                    'product' => json_encode($combinations)
                ]);
            }

            if ($product->prices()->where('printing', $type)->exists()) {
                $product->prices()->where('printing', $type)->delete();
            }
            $prices = $product->prices()->create([
                'price' => json_encode($result),
                'printing' => $type
            ]);
        }
        echo $prices;
    }
}
