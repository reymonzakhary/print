<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>{{ __('order') }} {{ $order->order_nr }}</title>
</head>

<body>
<div id="{{ $order->order_nr }}"
     class="z-0 w-full p-4 mx-auto text-sm text-black bg-white rounded shadow-md shadow-gray-500 md:w-1/2">

    <div class="flex flex-wrap w-full p-2 font-mono text-sm bg-gray-100 rounded">
        <div class="w-1/2">{{ __('created at') }}</div>
        <div class="w-1/2">{{ $order->created_at }}</div>
        <div class="w-1/2"> {{ __('context name') }} </div>
        <div class="w-1/2">{{ $order->context->name }}</div>
        <div class="w-1/2"> {{ __('context id') }} </div>
        <div class="w-1/2">{{ $order->context->id }}</div>
    </div>

    <!-- order customer data -->
    <h2 class="mt-4 font-bold"> {{ __('Customer') }} </h2>
    <section id="{{ $order->orderedBy?->id }}" class="grid grid-cols-2 mt-2">
        <div class="text-xs font-semibold text-gray-500 uppercase"> {{ __('name') }} </div>
        <div>{{ $order->orderedBy?->profile->first_name }} {{ $order->orderedBy?->profile->last_name }}</div>
        <div class="text-xs font-semibold text-gray-500 uppercase"> {{ __('email') }} </div>
        <div>{{ $order->orderedBy?->email }}</div>

        @if ($invoiceAddress = $order->orderedBy?->invoiceAddress())
            <div class="text-xs font-semibold text-gray-500 uppercase">{{ __('invoice_address') }}</div>
            <div class="grid grid-cols-2 ">
                <div class="text-xs font-semibold text-gray-500 uppercase">{{ __('address') }}</div>
                <div>{{ $invoiceAddress->getAttribute('address') }}</div>
                <div class="text-xs font-semibold text-gray-500 uppercase">{{ __('number') }}</div>
                <div>{{ $invoiceAddress->getAttribute('number') }}</div>
                <div class="text-xs font-semibold text-gray-500 uppercase">{{ __('city') }}</div>
                <div>{{ $invoiceAddress->getAttribute('city') }}</div>
                <div class="text-xs font-semibold text-gray-500 uppercase">{{ __('region') }}</div>
                <div>{{ $invoiceAddress->getAttribute('region') }}</div>
                <div class="text-xs font-semibold text-gray-500 uppercase">{{ __('zip code') }}</div>
                <div>{{ $invoiceAddress->getAttribute('zip_code') }}</div>
                <div class="text-xs font-semibold text-gray-500 uppercase">{{ __('phone number') }}</div>
                <div>{{ $invoiceAddress->pivot->getAttribute('phone_number') }}</div>
            </div>
        @endif
    </section>

    <!-- invoice info -->
    <h2 class="mt-4 font-bold">{{ __('Invoice') }}</h2>
    <section class="grid grid-cols-2 mt-2">
        @if ($order->order_nr)
            <div class="text-xs font-semibold text-gray-500 uppercase"> {{ __('order number') }} </div>
            <div>{{ $order->order_nr }}</div>
        @endif

        <div class="text-xs font-semibold text-gray-500 uppercase"> {{ __('status') }} </div>
        <div>{{ \App\Enums\Status::getStatusByCode($order->st)?->name }}</div>
        <div class="text-xs font-semibold text-gray-500 uppercase"> {{ __('shipping cost') }} </div>
        <div>{{ $order->shipping_cost / 100 }}</div>
        <div class="text-xs font-semibold text-gray-500 uppercase"> {{ __('note') }} </div>
        <div>{{ $order->note }}</div>
        <div class="text-xs font-semibold text-gray-500 uppercase"> {{ __('price') }} </div>
        <div>{{ $order->price }}</div>



        <div class="text-xs font-semibold text-gray-500 uppercase"> {{ __('delivery multiple') }} </div>
        <div>{{ (string) $order->delivery_multiple ? 'true' : 'false' }}</div>

        <div class="text-xs font-semibold text-gray-500 uppercase"> {{ __('delivery pickup') }} </div>
        <deliveryPickup>{{ $order->delivery_pickup ? 'true' : 'false' }}</deliveryPickup>
    </section>

    @if ($order->discount)
        <h2 class="mt-4 font-bold"> {{ __('Discount') }} </h2>
        <section class="grid grid-cols-2 mt-2 ">
            <div class="text-xs font-semibold text-gray-500 uppercase"> {{ __('type') }} </div>
            <div>{{ $order->discount->type }}</div>
            @if ($order->discount->type == 'fixed')
                <div class="text-xs font-semibold text-gray-500 uppercase"> {{ __('value') }} </div>
                <div>{{ $order->discount->value }}</div>
                <div class="text-xs font-semibold text-gray-500 uppercase"> {{ __('discount value') }} </div>
                <div>{{ (new \App\Plugins\Moneys())->setAmount($order->discount->value)->format() }}</div>
            @else
                <div class="text-xs font-semibold text-gray-500 uppercase"> {{ __('value') }} </div>
                <div>{{ $order->discount->value }} %</div>
            @endif
        </section>
    @endif

    @foreach ($order->services as $service)
        <h2 class="mt-4 text-lg font-bold"> {{ __('Service') }} </h2>
        <section class="grid grid-cols-2 mt-2 ">
            <div class="text-xs font-semibold text-gray-500 uppercase"> {{ __('name') }} </div>
            <div>{{ $service->name }}</div>

            <div class="text-xs font-semibold text-gray-500 uppercase"> {{ __('description') }} </div>
            <div>{{ $service->description }}</div>

            <div class="text-xs font-semibold text-gray-500 uppercase"> {{ __('price') }} </div>
            <div>{{ $service->price }}</div>
        </section>
    @endforeach

    <h2 class="mt-4 font-bold">{{ __('Products') }}</h2>
    @foreach ($order->items as $item)
        <section class="p-2 mt-2 bg-gray-100 rounded">

            <!-- product info -->
            @php($priceTable = $item->product['price'])
                <h3 class="flex text-sm font-bold">
                    <div>{{ $item->product['category']['name'] }}</div>
                </h3>

                <section class="grid grid-cols-2 mt-2">
                    <div class="text-xs font-semibold text-gray-500 uppercase"> {{ __('quantity') }} </div>
                    <div>{{ $priceTable['qty'] }}</div>

                    <div class="text-xs font-semibold text-gray-500 uppercase"> {{ __('vat') }} </div>
                    <div>{{ $item->vat ? $item->vat . '%' : '' }}</div>

                    <div class="text-xs font-semibold text-gray-500 uppercase"> {{ __('status') }} </div>
                    <div>{{ \App\Enums\Status::getStatusByCode($item->st)?->name }}</div>

                    <div class="text-xs font-semibold text-gray-500 uppercase"> {{ __('note') }} </div>
                    <div>{{ $item->note }}</div>
                </section>

                <h4 class="mt-4 text-xs font-bold">{{ __('Product Aspects') }}</h4>
                <div class="mt-2">
                    @foreach ($item->product['items'] as $object)
                        {{-- <div>{{__('item')}}</div> --}}
                        <section class="grid grid-cols-2 ">
                            <div class="text-xs font-semibold text-gray-500 uppercase">
                                {{ getDisplayName($object['key_display_name'], $iso) }}
                            </div>
                            <div>{{ getDisplayName($object['value_display_name'], $iso) }}</div>
                        </section>
                    @endforeach
                </div>

                <!-- files attached to item -->
                <h4 style="font-size:14px"> {{ __('Files') }} </h4>
                @if (($itemsMedia = $item->getMedia('items')) && $itemsMedia->count() > 0)
                    @foreach ($item->getMedia('items') as $media)
                        <section style="">

                            <div> {{ __('File') }} </div>
                            <div>
                                <div
                                    style="display: inline-block; color: gray; width: 200px; font-weight: 400; text-transform: uppercase; font-size: 12px">
                                    {{ __('Url') }} </div>
                                <div style="display: inline-block">
                                    {{ $media->getPublicFileUrl('tenant', $media->path, $media->name) }}
                                </div>
                            </div>

                        </section>
                    @endforeach
                @else
                    <div>No files are attached</div>
                @endif

                @if (!$item->delivery_separated)
                    @foreach ($item->addresses as $address)
                        <h5 class="mt-4 text-sm font-bold"> {{ __('Delivery Addresses') }} </h5>
                        <div>
                            <h4 class="mt-4 text-xs font-bold"> {{ __('Delivery Address') }} </h4>
                            <section class="grid grid-cols-2 ">
                                <div class="text-xs font-semibold text-gray-500 uppercase">
                                    {{ __('Del Add Company Name') }} </div>
                                <div>{{ $address->getoriginal('pivot_company_name') }}</div>
                                <div class="text-xs font-semibold text-gray-500 uppercase">
                                    {{ __('Del Add Company Contact Person') }}
                                </div>
                                <div>{{ $address->getoriginal('pivot_full_name') }}</div>
                                <div class="text-xs font-semibold text-gray-500 uppercase">
                                    {{ __('Del Address House Number') }} </div>
                                <div>{{ trim($address->number) }}</div>
                                <div class="text-xs font-semibold text-gray-500 uppercase">
                                    {{ __('Del Add Zip Code') }} </div>
                                <div>{{ $address->zip_code }}</div>
                                <div class="text-xs font-semibold text-gray-500 uppercase"> {{ __('Del Add City') }}
                                </div>
                                <div>{{ $address->city }}</div>
                                <div class="text-xs font-semibold text-gray-500 uppercase">
                                    {{ __('Del Add Quantity') }} </div>
                                <div>{{ $priceTable['qty'] }}</div>
                            </section>
                        </div>
                    @endforeach
                @elseif($item->delivery_separated)
                    @foreach ($item->children as $children)
                        <h5 class="mt-4 text-sm font-bold"> {{ __('Delivery Addresses') }} </h5>
                        <div>
                            <h4 class="mt-4 text-xs font-bold"> {{ __('Delivery Address') }} </h4>
                            <section class="grid grid-cols-2 ">
                                <div class="text-xs font-semibold text-gray-500 uppercase">
                                    {{ __('Del Add Company Name') }} </div>
                                <div>{{ $children->addresses[0]->getoriginal('pivot_company_name') }}</div>
                                <div class="text-xs font-semibold text-gray-500 uppercase">
                                    {{ __('Del Add Company Contact Person') }}
                                </div>
                                <div>{{ $children->addresses[0]->getoriginal('pivot_full_name') }}</div>
                                <div class="text-xs font-semibold text-gray-500 uppercase">
                                    {{ __('Del Address House Number') }} </div>
                                <div>{{ $children->addresses[0]->number }}</div>
                                <div class="text-xs font-semibold text-gray-500 uppercase">
                                    {{ __('Del Add Zip Code') }} </div>
                                <div>{{ $children->addresses[0]->zip_code }}</div>
                                <div class="text-xs font-semibold text-gray-500 uppercase"> {{ __('Del Add City') }}
                                </div>
                                <div>{{ $children->addresses[0]->city }}</div>
                                <div class="text-xs font-semibold text-gray-500 uppercase">
                                    {{ __('Del Add Quantity') }} </div>
                                <div>{{ $children->qty }}</div>
                            </section>
                        </div>
                    @endforeach
                @endif
        </section>

        </section>
    @endforeach

    @if (!$order->delivery_multiple)
        @foreach ($order->address as $address)
            <h5 class="mt-4 text-sm font-bold"> {{ __('Delivery Addresses') }} </h5>
            <div>
                <h4 class="mt-4 text-xs font-bold"> {{ __('Delivery Address') }} </h4>
                <section class="grid grid-cols-2 ">
                    <div> {{ __('Del Add Company Name') }} </div>
                    <div>{{ $address->getoriginal('pivot_company_name') }}</div>
                    <div> {{ __('Del Add Company Contact Person') }} </div>
                    <div>{{ $address->getoriginal('pivot_full_name') }}</div>
                    <div> {{ __('Del Address House Number') }} </div>
                    <div>{{ trim($address->number) }}</div>
                    <div> {{ __('Del Add Zip Code') }} </div>
                    <div>{{ $address->zip_code }}</div>
                    <div> {{ __('Del Add City') }} </div>
                    <div>{{ $address->city }}</div>
                </section>
            </div>
        @endforeach
    @endif
</div>
</body>

</html>
