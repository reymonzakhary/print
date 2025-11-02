<?php

declare(strict_types=1);

namespace App\DTO\Tenant\Orders\Transaction;

use App\Actions\PriceAction\CalculationAction;
use App\Enums\Status;
use App\Enums\Transaction\TransactionType;
use App\Facades\Settings;
use App\Models\Tenants\Address;
use App\Models\Tenants\Item;
use App\Models\Tenants\Member;
use App\Models\Tenants\Order;
use App\Models\Tenants\Service;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/**
 * @method static array|null toCustomField(Order $order)
 * @method static array|null toTransaction(Order $order)
 */
final readonly class TransactionDTO
{
    /**
     * @param Order $order
     *
     * @return array
     */
    private function customField(
        Order $order,
    ): array
    {

        return [
            'order_nr' => $order->getAttribute('order_nr'),
            'customer' => (static function () use ($order): array {
                $member = $order->orderedBy()->first();
                $customer = $order->customer ?? [];
                $order_invoice_address = $order->invoice_address()->first();
                return [
                    'type' => $order->internal ?  $member?->getAttribute('type') : 'external',
                    'email' => (empty($customer)  ? $member?->getAttribute('email') : $customer['email'] ) ?? null ,
                    'full_name' => ( empty($customer)  ? $member?->fullname() : $customer['user']['first_name'] . ' ' . $customer['user']['last_name'] ) ?? null,
                    'company_name' => ( empty($customer) ? $member?->company()?->getAttribute('name') : $customer['company']['name']) ?? null   ,
                    'address_data' => [
                        'address' => $order_invoice_address?->getAttribute('address'),
                        'number' => $order_invoice_address?->getAttribute('number'),
                        'zip_code' => $order_invoice_address?->getAttribute('zip_code'),
                        'city' => $order_invoice_address?->getAttribute('city'),
                        'country' => $order_invoice_address?->country()->firstOrFail()->getAttribute('name'),
                        'tax_nr' => $order_invoice_address?->pivot->getAttribute('tax_nr'),
                    ],
                ];
            }
            )(),
            'supplier' => (static function () use ($order): array {
                $supplier_custom_data = tenantCustomFields();
                $context_address = $order->context()->firstOrFail()->addresses()->first();
                return [
                    'name' => $supplier_custom_data->pick('company_name') ?? null,
                    'coc' => $supplier_custom_data->pick('coc') ?? null,
                    'tax_nr' => $supplier_custom_data->pick('tax_nr') ?? null,
                    'address_data' => [
                        'address' => $context_address?->getAttribute('address'),
                        'number' => $context_address?->getAttribute('number'),
                        'zip_code' => $context_address?->getAttribute('zip_code'),
                        'city' => $context_address?->getAttribute('city'),
                        'country' => $context_address?->country()->first()?->getAttribute('name'),
                    ],

                ];
            })(),
            'products' => $order
                ->items()
                ->whereStatusIsNotCancelled()
                ->get()
                ->merge($order->services()->get())
                ->map(
                    static function (Item|Service $product): array {
                        $quantity = $product->pivot->getAttribute('qty');
                        $shipping_cost = (float)$product->pivot->getAttribute('shipping_cost');
                        $subtotal_price = match (true) {
                            $product instanceof Item => (float)$product->getAttribute('product')['price']['gross_price'],
                            $product instanceof Service => ((float)$product->getAttributes()['price']) * $quantity,
                        };
                        return [
                            'type' => match (true) {
                                $product instanceof Item => 'item',
                                $product instanceof Service => 'service',
                            },

                            'name' => match (true) {
                                $product instanceof Item => $product->getAttribute('product')['category']['name'],
                                $product instanceof Service => $product->getAttribute('name'),
                            },

                            'description' => match (true) {
                                $product instanceof Item => null,
                                $product instanceof Service => $product->getAttribute('description'),
                            },

                            'supplier_id' => match (true) {
                                $product instanceof Item => $product->getAttribute('supplier_id'),
                                $product instanceof Service => null,
                            },

                            'supplier_name' => match (true) {
                                $product instanceof Item => $product->getAttribute('supplier_name'),
                                $product instanceof Service => null,
                            },

                            'children_count' => match (true) {
                                $product instanceof Item => $product->children()->count(),
                                $product instanceof Service => null,
                            },

                            'unit_price' => match (true) {
                                $product instanceof Item => (float)$product->getAttribute('product')['price']['gross_ppp'],
                                $product instanceof Service => (float)$product->getAttributes()['price'],
                            },

                            'vat' => match (true) {
                                $product instanceof Item => (string)(float)$product->getAttribute('product')->price['vat'],
                                $product instanceof Service => (string)(float)$product->pivot->getAttribute('vat')
                            },

                            'subtotal' => $subtotal_price,
                            'shipping_cost' => $shipping_cost,
                            'subtotal_incl_shipping_cost' => $subtotal_price + $shipping_cost,
                            'quantity' => $quantity,

                        ];
                    }
                )
                ->toArray(),

            'vats' => (function () use ($order): array {
                $vats = [];

                $productsPriceArray = Collection::make($order->getAttribute('items_price_array'))
                    ->merge(
                        Collection::make($order->getAttribute('order_services_price_array'))
                    );

                foreach ($productsPriceArray as $productData) {
                    $vatData = $productData['vat'];

                    $vatIdentifier = (string)(float)$vatData['vat_percentage'];

                    $vats[$vatIdentifier] = !array_key_exists($vatIdentifier, $vats)
                        ? $vatData['vat']
                        : $vats[$vatIdentifier] + $vatData['vat'];
                }

                return $vats;
            })(),
            'total_ex' => $order->getAttribute('subTotal_price'),
            'total_incl_vat' => $order->getAttribute('total_price'),
        ];
    }

    /**
     * Export to a database applicable format
     *
     * @param Order $order
     *
     * @return array
     */
    private function transaction(
        Order $order
    ): array
    {
        return array_filter([
            'order_id' => $order->getAttribute('id'),
            'payment_method' => null,
            'st' => Status::DRAFT,
            'fee' => 0,
            'vat' => $order->vat()->first()?->getAttribute('percentage'),
            'discount_id' => $order->getAttribute('discount_id'),
            'price' => 0, # TEMP measurement as sometimes, we have a float numbers and this field only accepts `bigint` on the DB
            'custom_field' => $this->customField($order),
            'company_id' => null,
            'team_id' => $order->getAttribute('team_id'),
            'user_id' => $order->getAttribute('user_id'),
            'contract_id' => null,
            'type' => TransactionType::SINGLE,
            'counter' => 0,
            'level' => 0,
            'due_date' => Carbon::now()->addDays(Settings::quotationExpiresAfter()->value),
        ]);
    }

    /**
     * Handles the static method calls dynamically.
     *
     * @param string $name The name of the method being called.customField
     * @param array $arguments The arguments passed to the method.
     *
     * @return mixed The result of the dynamically called method.
     */
    public static function __callStatic(string $name, array $arguments)
    {
        $method = Str::replace('to', '', Str::camel($name));

        return (new self())->{$method}(...$arguments);
    }
}
