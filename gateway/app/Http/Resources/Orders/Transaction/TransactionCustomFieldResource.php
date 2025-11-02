<?php

declare(strict_types=1);

namespace App\Http\Resources\Orders\Transaction;

use App\Plugins\Moneys;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class TransactionCustomFieldResource extends JsonResource
{
    public function toArray(
        Request $request
    ): array
    {
        try {
            $total_shipping_cost = 0;
            $subtotal = 0;
            foreach ($this->resource->pick('products') as $productData) {
                $total_shipping_cost += $productData['shipping_cost'];
                $subtotal += $productData['subtotal'];
            }
            return [
                'supplier' => $this->resource->pick('supplier'),
                'customer' => $this->resource->pick('customer'),

                'order_nr' => $this->resource->pick('order_nr'),

                'products' => (function (): array {
                    $result = [];

                    foreach ($this->resource->pick('products') as $productData) {
                        $result[] = array_merge($productData, [
                            'display_unit_price' => \moneys()
                                ->setPrecision(5)
                                ->setAmount($productData['unit_price'])
                                ->format(),

                            'display_subtotal' => \moneys()
                                ->setAmount($productData['subtotal'])
                                ->format(),
                            'display_shipping_cost' => \moneys()
                                ->setAmount($productData['shipping_cost'])
                                ->format(),

                            'display_total_incl_shipping_cost' => \moneys()
                                ->setAmount($productData['subtotal_incl_shipping_cost'])
                                ->format()
                        ]);
                    }

                    return $result;
                })(),

                'vats' => (function (): array {
                    $result = [];

                    foreach ($this->resource->pick('vats') as $vatPercentage => $vatValue) {
                        $vatValue = is_array($vatValue) ? 0 : $vatValue;
                        $result[] = [
                            'vat' => $vatValue,
                            'vat_percentage' => $vatPercentage,
                            'vat_display' => \moneys()
                                ->setAmount($vatValue * 100)
                                ->format(),
                        ];
                    }

                    return $result;
                })(),

                'display_total_ex' => \moneys()
                    ->setAmount($this->resource->pick('total_ex'))
                    ->format(),

                'display_incl_vat' => \moneys()
                    ->setAmount($this->resource->pick('total_incl_vat'))
                    ->format(),

                'display_total_shipping_cost' => \moneys()
                ->setAmount($total_shipping_cost)
                ->format(),

                'display_subtotal' => \moneys()
                ->setAmount($subtotal)
                ->format()
            ];
        } catch (\Exception $exception) {
            return [];
        }
    }
}
