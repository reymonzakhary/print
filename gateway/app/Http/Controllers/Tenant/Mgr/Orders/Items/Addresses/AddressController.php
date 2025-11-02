<?php

declare(strict_types=1);

namespace App\Http\Controllers\Tenant\Mgr\Orders\Items\Addresses;

use App\Events\Tenant\Order\Item\Address\UpdateOrderItemAddressEvent;
use App\Http\Controllers\Controller;
use App\Http\Requests\Addresses\StoreOrderItemAddressRequest;
use App\Http\Resources\Items\OrderItemResource;
use App\Models\Tenants\Item;
use App\Models\Tenants\Order;
use Illuminate\Validation\ValidationException;

final class AddressController extends Controller
{
    /**
     * Update an address for an order item
     *
     * @param Order $order
     * @param Item $item
     * @param StoreOrderItemAddressRequest $request
     *
     * @return OrderItemResource
     *
     * @throws ValidationException
     */
    public function update(
        Order $order,
        Item $item,
        StoreOrderItemAddressRequest $request
    ): OrderItemResource
    {
        if ($order->items()->where('items.id', $item->getAttribute('id'))->exists()) {
            if ($order->getAttribute('delivery_multiple')) {
                $qty = $item->getAttribute('product')['quantity'];

                if ($item->getAttribute('delivery_separated') && $request->input('addresses')) {
                    $itemsQty = collect($request->input('addresses'))->sum(
                        function (array $address): int {
                            return $address['qty'];
                        }
                    );

                    $originalQty = $item->getAttribute('product');

                    if ($itemsQty > $originalQty['price']['qty']) {
                        throw ValidationException::withMessages([
                            'qty' =>
                                __('addresses.total_quantity_validation')
                        ]);
                    }

                    $qty = $originalQty['price']['qty'] - $itemsQty;

                    collect($item->children()->get())->map(
                        function (Item $orderItem): void {
                            $orderItem->addresses()->detach();
                            $orderItem->delete();
                        }
                    );

                    collect($request->input('addresses'))->map(
                        function (array $address) use ($item, $order, $request): void {
                            $orderItem = $item->children()->create([
                                'qty' => (int)$address['qty'],
                                'delivery_pickup' => $address['delivery_pickup'],
                                'shipping_cost' => $address['shipping_cost'] ?? 0
                            ]);

                            $orderItem->addresses()->sync([
                                    $address['address'] => [
                                        'type' => $request->input('address_type'),
                                        'full_name' => $request->input('address_full_name'),
                                        'company_name' => $request->input('address_company_name'),
                                        'phone_number' => $request->input('address_phone_number'),
                                        'tax_nr' => $request->input('address_tax_nr'),
                                        'team_address' => $request->boolean('address_team_address'),
                                        'team_id' => $request->input('address_team_id'),
                                        'team_name' => $request->input('address_team_name')
                                    ]
                                ]
                            );

                            event(new UpdateOrderItemAddressEvent($address['address'], $order, $item, auth()->user()));
                        }
                    );
                } elseif (!$item->getAttribute('delivery_separated') && $request->input('address')) {
                    collect($item->children()->get())->map(
                        function (Item $orderItem): void {
                            $orderItem->addresses()->detach();
                            $orderItem->delete();
                        }
                    );

                    $item->addresses()->sync([
                            $request->input('address') => [
                                'type' => $request->input('address_type'),
                                'full_name' => $request->input('address_full_name'),
                                'company_name' => $request->input('address_company_name'),
                                'phone_number' => $request->input('address_phone_number'),
                                'tax_nr' => $request->input('address_tax_nr'),
                                'team_address' => $request->boolean('address_team_address'),
                                'team_id' => $request->input('address_team_id'),
                                'team_name' => $request->input('address_team_name')
                            ]
                        ]
                    );

                    event(new UpdateOrderItemAddressEvent($request->input('address'), $order, $item, auth()->user()));
                }

                $order->items()->updateExistingPivot($item, ['qty' => (int)$qty]);
            }
        }

        return OrderItemResource::make(
            $order->items()->where('items.id', $item->getAttribute('id'))->first()
        );
    }
}
