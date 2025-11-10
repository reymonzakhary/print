<?php

namespace App\Http\Controllers\Tenant\Mgr\Quotations\Items\Addresses;

use App\Events\Tenant\Order\Item\Address\UpdateOrderItemAddressEvent;
use App\Http\Controllers\Controller;
use App\Http\Requests\Addresses\StoreItemAddressRequest;
use App\Http\Resources\Items\QuotationItemResource;
use App\Models\Tenant\Item;
use App\Models\Tenant\Quotation;
use Illuminate\Validation\ValidationException;

class AddressController extends Controller
{

    /**
     * @param Quotation $quotation
     * @param Item $item
     * @param StoreItemAddressRequest $request
     * @return QuotationItemResource
     * @throws ValidationException
     */
    public function update(
        Quotation $quotation,
        Item $item,
        StoreItemAddressRequest $request
    ): QuotationItemResource {
        if ($quotation->items()->where('items.id', $item->id)->exists()) {
            if ($quotation->delivery_multiple) {
                $qty = $item->product->quantity;

                if ($item->delivery_separated && $request->addresses) {
                    $itemsQty = collect($request->addresses)->sum(function ($address) {
                        return $address['qty'];
                    });

                    $originalQty = $item->product;

                    if ($itemsQty > $originalQty['price']['qty']) {
                        throw ValidationException::withMessages([
                            'qty' =>
                                __('addresses.total_quantity_validation')
                        ]);
                    }

                    $qty = $originalQty['price']['qty'] - $itemsQty;

                    collect($item->children()->get())->map(function ($quotationItem) {
                        $quotationItem->addresses()->detach();
                        $quotationItem->delete();
                    });

                    collect($request->addresses)->map(function ($address) use ($item, $quotation, $request) {
                        $quotationItem = $item->children()->create([
                            'qty' => (int)$address['qty'],
                            'delivery_pickup' => $address['delivery_pickup'],
                            'shipping_cost' => $address['shipping_cost'] ?? 0
                        ]);

                        $quotationItem->addresses()->sync([
                            $address['address'] => [
                                'type' => $request->address_type,
                                'full_name' => $request->address_full_name,
                                'company_name' => $request->address_company_name,
                                'phone_number' => $request->address_phone_number,
                                'tax_nr' => $request->address_tax_nr,
                                'team_address' => $request->boolean('address_team_address'),
                                'team_id' => $request->address_team_id,
                                'team_name' => $request->address_team_name
                            ]
                        ]);

                        $quotationItem->addresses()->sync([$address['address'] => ['type' => 'work']]);

                        event(new UpdateOrderItemAddressEvent($address['address'], $quotation, $item, auth()->user()));
                    });
                } elseif (!$item->delivery_separated && $request->address) {
                    collect($item->children()->get())->map(function ($quotationItem) {
                        $quotationItem->addresses()->detach();
                        $quotationItem->delete();
                    });

                    $item->addresses()->sync([
                        $request->address => [
                            'type' => $request->address_type,
                            'full_name' => $request->address_full_name,
                            'company_name' => $request->address_company_name,
                            'phone_number' => $request->address_phone_number,
                            'tax_nr' => $request->address_tax_nr,
                            'team_address' => $request->boolean('address_team_address'),
                            'team_id' => $request->address_team_id,
                            'team_name' => $request->address_team_name
                        ]
                    ]);

                    event(new UpdateOrderItemAddressEvent($request->address, $quotation, $item, auth()->user()));
                }

                $quotation->items()->updateExistingPivot($item, ['qty' => (int)$qty]);
            }
        }

        return QuotationItemResource::make($quotation->items()->where('items.id', $item->id)->first())->hide([]);
    }
}
