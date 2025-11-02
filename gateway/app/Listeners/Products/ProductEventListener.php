<?php

namespace App\Listeners\Products;

use App\Contracts\Cartesian\CartesianProduct;
use App\Events\Tenant\Products\CreateProductCombinationEvent;
use App\Events\Tenant\Products\DeleteProductCombinationEvent;
use App\Events\Tenant\Products\FinishedProductCombinationEvent;
use App\Services\Suppliers\SupplierProductService;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;

class ProductEventListener implements ShouldQueue
{
    public function __construct(public SupplierProductService $supplierProductService)
    {
    }

    /**
     * @param $event
     * @throws GuzzleException
     */
    public function onProductBoopsCreated(
        $event
    )
    {

        $tenant_id = $event->tenant;
        $tenant_name = $event->tenant_name;
        $data = $event->request['boops'];
        foreach ($data as $i => $value) {
            if (!$value['ops']) {
                unset($data[$i]);
            }
        }
        $event->request['boops'] = $data;
        $list = [];

        $mapFunction = static function ($item) use (&$list) {
            $list[$item['slug']] = array_map(static function ($option) use ($item) {
                return [
                    // 'name'=>$option['name'], 'excludes'=> $option['excludes']
                    'key_link' => $item['linked'],
                    'key' => $item['slug'], //'format', => 'boobs->obj->slug'
                    'display_key' => $item['display_name'], // 'Format', => 'boobs->obj->display_name'
                    'box_id' => $item['id'], //'format', =>

                    'value_link' => $option['linked'],
                    'value' => $option['slug'], // 'a5', => 'boobs->obj->ops->slug'
                    'display_value' => $option['display_name'], // 'A', => 'boobs->obj->ops->display_name'
                    'option_id' => $option['id'],

                    'excludes' => $option['excludes']
                ];
            }, collect($item['ops'])->toArray());
        };
        array_walk($event->request['boops'], $mapFunction);
        $cartesianProduct = new CartesianProduct($list);

        $productList = [];

        foreach ($cartesianProduct as $index => $object) {
            // if(in_array(null,$object)){continue;}
            $arr1 = array_column($object, 'value_link');
            $skip = false;
            foreach ($object as &$item) {
                // dd($index $object, $item);

                if ($item && isset($item['excludes']) && count($item['excludes'])) {
                    foreach ($item['excludes'] as $exclude) {
                        $ex_count = count($exclude);
                        if ($ex_count === count(array_intersect($exclude, $arr1))) {
                            $skip = true;
                            continue;
                        }
                    }
                }
                if (is_array($item) && array_key_exists('excludes', $item)) {
                    unset($item['excludes']);
                }
            }
            if (!$skip) {
                $productList[] = [
                    'linked' => $event->request['linked'],
                    "tenant_id" => $tenant_id,
                    "tenant_name" => $tenant_name,
                    'category_name' => $event->request['name'],
                    'supplier_category' => $event->request['supplier_category'],
                    'category_display_name' => $event->request['display_name'] ?? $event->request['name'],
                    'category_slug' => $event->category,
                    'object' => $object
                ];
                if ($index % 10 === 0) {
                    $this->supplierProductService->obtainGenerateProducts(
                        $event->category, ['products' => $productList], $tenant_id,
                        $event->host_id
                    );
                    $productList = [];
                }
            }
        }
        if (count($productList) > 0) {
            $this->supplierProductService->obtainGenerateProducts(
                $event->category, ['products' => $productList], $tenant_id, $event->host_id
            );
        }
        event(
            new FinishedProductCombinationEvent(
                (array)$this->supplierProductService->obtainCountGeneratedProducts($event->category, $tenant_id),
                $event->category
            )
        );
    }

    public function onProductsDelete($event)
    {
        $result = $this->supplierProductService->obtainReGenerateProducts($event->category, $event->tenant);

        if (optional($result)["status"] === 200) {
            event(new CreateProductCombinationEvent(
                    $event->category,
                    $event->boops,
                    $event->tenant,
                    $event->tenant_name,
                    $event->host_id
                )
            );
        }
    }

    /**
     * @param $events
     */
    public function subscribe($events)
    {
        $events->listen(
            CreateProductCombinationEvent::class,
            'App\Listeners\Products\ProductEventListener@onProductBoopsCreated'
        );
        $events->listen(
            DeleteProductCombinationEvent::class,
            'App\Listeners\Products\ProductEventListener@onProductsDelete'
        );
    }

    public function failed($event, $exception)
    {
        Log::info("ProductEventListener => .....", [$exception, $event]);
    }

}
