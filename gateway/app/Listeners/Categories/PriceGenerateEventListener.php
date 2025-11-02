<?php

namespace App\Listeners\Categories;

use App\Events\Tenant\Categories\FinishedPriceGenerateEvent;
use App\Events\Tenant\Categories\PriceGenerateEvent;
use App\Services\Categories\Products\Prices\PriceCalculationService;

class PriceGenerateEventListener
{

    public function __construct(public PriceCalculationService $supplierCategoryService)
    {

    }

    /**
     * Create the event listener.
     *
     * @return void
     */
    public function onPriceCombination($events)
    {
        $data = $this->supplierCategoryService->obtainStoreCombinationPrices($events->category, $events->uuid);
        event(new FinishedPriceGenerateEvent([
            'message' => __(optional($data)['count'] ? "{$data['count']} prices generated in category {$events->category}" : optional($data)['message']??''),
            'status' => optional($data)['status']?? 'success'
        ]));
    }


    public function subscribe($events)
    {
        $events->listen(
            PriceGenerateEvent::class,
            'App\Listeners\Categories\PriceGenerateEventListener@onPriceCombination'
        );
    }
}
