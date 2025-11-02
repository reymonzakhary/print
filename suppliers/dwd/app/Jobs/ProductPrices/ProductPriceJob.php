<?php

namespace App\Jobs\ProductPrices;

use App\Models\Product;
use App\Traits\CurlRequestTrait;
use function BenTools\CartesianProduct\cartesian_product;

class ProductPriceJob extends \App\Jobs\Job
{
    use CurlRequestTrait;

    /**
     * category ID
     * @var Int
     */
    public $category_id;

    /**
     * connection URL
     * @var String
     */
    public $url;

    /**
     * connection token
     * @var String
     */
    public $token;

    /**
     * model data
     * @var array
     */
    public $model;

    /**
     * product combination
     * @var array
     */
    public $combinations;

    /**
     * delivery data combination
     * @var array
     */
    public $delivery;

    /**
     * type data combination
     * @var String
     */
    public $type;

    /**
     * Create a new job instance.
     *
     * @param Int $category_id
     * @param String $url
     * @param array $model
     * @param array $combinations
     * @param array $delivery
     * @param String $type
     * @return void
     */
    public function __construct($category_id, $url, $model, $combinations, $delivery, $type)
    {
        $this->category_id = $category_id;

        $this->url = $url;

        $this->model = $model;

        $this->combinations = $combinations;

        $this->delivery = $delivery;

        $this->type = $type;

        $this->token = config('pcom.token');
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $req_combinations = (object) [];

        $result = [];

        foreach(cartesian_product($this->delivery) as $delivery)
        {
            $req_combinations->sku = $this->model->sku;

            $req_combinations->options = array_merge($this->combinations, $delivery);

            $req_combinations->shipments[]['copies'] = $delivery['copies'];

            $req_combinations->maxDesigns = $this->model->max_designs;

            $req_combinations->deliveryPromise = false;

            $response = $this->curlPostRequest( $this->url, json_encode($req_combinations) );

            if( $response['code'] == 200 )
            {
                $result[]['price'] = $response['data'];
                $result[]['delivery'] = $delivery;
            }
        }

        if(count($result) > 0){
            /**
             * check existence
             */
            if(Product::whereJsonContains('product', $this->combinations)->exists())
            {
                $product = Product::whereJsonContains('product', $this->combinations)->first();
            }else{
                $product = Product::create([
                    'category_id' => $this->category_id,
                    'product' => json_encode($this->combinations)
                ]);
            }

            if($product->prices()->where('printing', $this->type)->exists())
            {
                $product->prices()->where('printing', $this->type)->delete();
            }
            $prices = $product->prices()->create([
                'price' => json_encode($result),
                'printing' => $this->type
            ]);
        }
        echo 'Done...';
    }

     /**
     * The job failed to process.
     *
     * @param  Exception  $exception
     * @return void
     */
    public function failed(Exception $exception)
    {
        dd($exception->getMessage());
    }
}
