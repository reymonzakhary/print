<?php

namespace App\Actions\PriceAction;

use App\Models\Tenants\Order;
use App\Models\Tenants\Quotation;
use Illuminate\Database\Eloquent\Model;
use App\Plugins\Moneys;

final class CalculationAction
{
    protected int $subTotal = 0;
    protected int $total = 0;
    protected float $services = 0;
    protected array $order_services = [];
    protected float $order_services_price = 0;
    protected float $vats = 0;
    protected array $items = [];
    protected float $shipping_cost = 0;

    public function __construct(
        private readonly Order|Quotation|Model $model
    ) {
        $this->items();
    }

    public function items(): self
    {
        $itemPrice = 0;
        $itemVats = 0;
        $itemServices = 0;
        $shipping_cost = 0;

        foreach ($this->model->items()->whereStatusIsNotCancelled()->get() as $item) {
            /**
             * Item Price
             */
            if (!optional($item->product)['price']) {
                return $this;
            }

            $item_price = optional($item->product?->price)['selling_price_ex'] ?? 0;
            $item_shipping_cost = $item->pivot?->shipping_cost ?? 0;

            // Calculate the selling price including shipping - store in variable for efficiency
            $selling_price_inc_shipping = $item_price + $item_shipping_cost;

            // Update the product JSON with the calculated value
            // For AsArrayObject cast, we need to modify the object directly
            $item->product['selling_price_inc_shipping'] = $selling_price_inc_shipping;

            // Save the changes to database
            $item->withoutEvents(function () use ($item) {
                $item->save();
            });

            /**
             * Vat Calculation - using our calculated value instead of accessing $item->product
             */
            $percentage = optional($item->product?->price)['vat'] ?? 0;
            $vat = moneys()->setAmount($selling_price_inc_shipping)->setTax($percentage);

            /**
             * Services Calculation
             */
            $services_price = 0;
            $services_vat_price = 0;

            foreach ($item->services as $services) {
                $services_price += $services->price->amount();
            }
            $services_price = moneys()->setAmount($services_price);

            /**
             * Sum prices
             */
            $itemServices += $services_price->amount();
            $itemVats += $vat->multiply(100)->amount(false, true);
            $itemPrice += (float)$item_price;
            $shipping_cost += $item_shipping_cost;

            $this->items[] = [
                'item_id' => $item->id,
                'display_shipping_cost' => moneys()->setAmount($item_shipping_cost)->format(),
                'shipping_cost' => $item_shipping_cost,
                'selling_price_inc_shipping' => $selling_price_inc_shipping, // Use calculated value
                'selling_price_inc_shipping_display' => moneys()->setAmount($selling_price_inc_shipping)->format(),
                'vat' => [
                    'vat' => $vat->divide(100)->amount(false, true),
                    'vat_display' => $vat->format(false, true),
                    'vat_percentage' => $percentage,
                ],
                'services' => [
                    'service' => $services_price->amount(),
                    'service_display' => $services_price->format(),
                ]
            ];
        }

        $order_services_price = 0;
        $order_services_vat_price = 0;
        $order_services_price_array = [];

        $this->model->services()->get()->each(function($service) use (
            &$order_services_price,
            &$order_services_price_array,
            &$order_services_vat_price) {

            $services_price = moneys()->setAmount($service->price->amount() * $service->pivot->qty * 100)->setTax($service->pivot->vat);
            $order_services_price += $services_price->amount() * 100;
            $order_services_vat_price += $services_price->multiply(100)->amount(false, true);

            $order_services_price_array[] = [
                'service_id' => $service->id,
                'service_qty' => $service->pivot->qty,
                'display_service_cost' => $service->price->format(),
                'shipping_cost' => $service->price->amount(),
                'vat' => [
                    'vat' => $services_price->divide(100)->amount(false, true),
                    'vat_display' => $services_price->format(false, true),
                    'vat_percentage' => (float)$service->pivot->vat,
                ],
            ];
        });

        $this->subTotal = $itemPrice + $itemServices + $order_services_price + $shipping_cost;
        $this->total = $itemPrice + $itemVats + $order_services_price + $shipping_cost + $itemServices + $order_services_vat_price;
        $this->services = $itemServices;
        $this->order_services_price = $order_services_price;
        $this->order_services = $order_services_price_array;
        $this->vats = $itemVats + $order_services_vat_price;
        $this->shipping_cost = $shipping_cost;

        if (!$this->model->archived) {
            $this->model->update(['price' => $this->subTotal]);
        }

        return $this;
    }

    public function calculate(): Order|Quotation
    {
        $this->model->setAttribute('total_price', $this->total);
        $this->model->setAttribute('subTotal_price', $this->subTotal);
        $this->model->setAttribute('item_services_price', $this->services);
        $this->model->setAttribute('order_services_price_array', $this->order_services);
        $this->model->setAttribute('order_services_price', $this->order_services_price);
        $this->model->setAttribute('vats_price', $this->vats);
        $this->model->setAttribute('shipping_cost', $this->shipping_cost);
        $this->model->setAttribute('items_price_array', $this->items);

        // Clear and reload the items relationship to ensure fresh data
        $this->model->unsetRelation('items');
        $this->model->load(['items']);

        return $this->model;
    }

    public function getVats()
    {
        return $this->vats;
    }
}
