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
     style="background-color: white; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;">

    <div style="background-color: #e5e7eb; border-radius: .3rem; padding: .5rem;">
        <div
            style="display: inline-block; color: gray; width: 200px; font-weight: 400; text-transform: uppercase; font-size: 12px">
            {{ __('order id') }}</div>
        <div style="display: inline-block; font-family: monospace;">{{ $order->getAttribute('id') }}</div>
        <br>
        <div
            style="display: inline-block; color: gray; width: 200px; font-weight: 400; text-transform: uppercase; font-size: 12px">
            {{ __('created at') }}</div>
        <div style="display: inline-block; font-family: monospace;">{{ $order->created_at }}</div>
        <br>
        <div
            style="display: inline-block; color: gray; width: 200px; font-weight: 400; text-transform: uppercase; font-size: 12px">
            {{ __('context name') }}
        </div>
        <div style="display: inline-block">{{ $order->context->name }}</div>
        <br>
        <div
            style="display: inline-block; color: gray; width: 200px; font-weight: 400; text-transform: uppercase; font-size: 12px">
            {{ __('context id') }}
        </div>
        <div style="display: inline-block">{{ $order->context->id }}</div>
    </div>

    <!-- order customer data -->
    <h2 style="font-size: 16px"> {{ __('Customer') }} </h2>
    <section id="{{ $order->orderedBy?->id }}" style="">
        <div
            style="display: inline-block; color: gray; width: 200px; font-weight: 400; text-transform: uppercase; font-size: 12px">
            {{ __('name') }} </div>
        <div style="display: inline-block">{{ $order->orderedBy?->profile->first_name }}
            {{ $order->orderedBy?->profile->last_name }}</div>
        <br>
        <div
            style="display: inline-block; color: gray; width: 200px; font-weight: 400; text-transform: uppercase; font-size: 12px">
            {{ __('email') }} </div>
        <div style="display: inline-block">{{ $order->orderedBy?->email }}</div>

        @if ($invoiceAddress = $order->orderedBy?->invoiceAddress())
            <h2 style="font-size: 16px; margin-top:20px">{{ __('Invoice Address') }}</h2>
            <div style="">
                <div
                    style="display: inline-block; color: gray; width: 200px; font-weight: 400; text-transform: uppercase; font-size: 12px">
                    {{ __('address') }}</div>
                <div style="display: inline-block">{{ $invoiceAddress->getAttribute('address') }}</div>
                <br>
                <div
                    style="display: inline-block; color: gray; width: 200px; font-weight: 400; text-transform: uppercase; font-size: 12px">
                    {{ __('number') }}</div>
                <div style="display: inline-block">{{ $invoiceAddress->getAttribute('number') }}</div>
                <br>
                <div
                    style="display: inline-block; color: gray; width: 200px; font-weight: 400; text-transform: uppercase; font-size: 12px">
                    {{ __('city') }}</div>
                <div style="display: inline-block">{{ $invoiceAddress->getAttribute('city') }}</div>
                <br>
                <div
                    style="display: inline-block; color: gray; width: 200px; font-weight: 400; text-transform: uppercase; font-size: 12px">
                    {{ __('region') }}</div>
                <div style="display: inline-block">{{ $invoiceAddress->getAttribute('region') }}</div>
                <br>
                <div
                    style="display: inline-block; color: gray; width: 200px; font-weight: 400; text-transform: uppercase; font-size: 12px">
                    {{ __('zip code') }}</div>
                <div style="display: inline-block">{{ $invoiceAddress->getAttribute('zip_code') }}</div>
                <br>
                <div
                    style="display: inline-block; color: gray; width: 200px; font-weight: 400; text-transform: uppercase; font-size: 12px">
                    {{ __('phone number') }}</div>
                <div style="display: inline-block">{{ $invoiceAddress->pivot->getAttribute('phone_number') }}</div>
            </div>
        @endif
    </section>

    <!-- invoice info -->
    <h2 style="font-size: 16px; margin-top:20px">{{ __('Invoice') }}</h2>
    <section style="">
        @if ($order->order_nr)
            <div
                style="display: inline-block; color: gray; width: 200px; font-weight: 400; text-transform: uppercase; font-size: 12px">
                {{ __('order number') }} </div>
            <div style="display: inline-block">{{ $order->order_nr }}</div>
        @endif

        <br>
        <div
            style="display: inline-block; color: gray; width: 200px; font-weight: 400; text-transform: uppercase; font-size: 12px">
            {{ __('status') }} </div>
        <div style="display: inline-block">{{ \App\Enums\Status::getStatusByCode($order->st)?->name }}</div>
        <br>
        <br>
        <div
            style="display: inline-block; color: gray; width: 200px; font-weight: 400; text-transform: uppercase; font-size: 12px">
            {{ __('shipping cost') }} </div>
        <div style="display: inline-block">{{ $order->shipping_cost / 100 }}</div>
        <br>
        <div
            style="display: inline-block; color: gray; width: 200px; font-weight: 400; text-transform: uppercase; font-size: 12px">
            {{ __('note') }} </div>
        <div style="display: inline-block">{{ $order->note }}</div>
        <br>
        <div
            style="display: inline-block; color: gray; width: 200px; font-weight: 400; text-transform: uppercase; font-size: 12px">
            {{ __('price') }} </div>
        <div style="display: inline-block">{{ $order->price }}</div>

        <br>

        <div
            style="display: inline-block; color: gray; width: 200px; font-weight: 400; text-transform: uppercase; font-size: 12px">
            {{ __('delivery multiple') }} </div>
        <div style="display: inline-block">{{ (string) $order->delivery_multiple ? 'true' : 'false' }}</div>
        <br>
        <div
            style="display: inline-block; color: gray; width: 200px; font-weight: 400; text-transform: uppercase; font-size: 12px">
            {{ __('delivery pickup') }} </div>
        <div style="display: inline-block">{{ $order->delivery_pickup ? 'true' : 'false' }}</div>
    </section>

    @if ($order->discount)
        <h2 style="font-size: 16px; margin-top:20px"> {{ __('Discount') }} </h2>
        <section style="background-color">
            <div
                style="display: inline-block; color: gray; width: 200px; font-weight: 400; text-transform: uppercase; font-size: 12px">
                {{ __('type') }} </div>
            <div style="display: inline-block">{{ $order->discount->type }}</div>
            <br>
            @if ($order->discount->type == 'fixed')
                <div
                    style="display: inline-block; color: gray; width: 200px; font-weight: 400; text-transform: uppercase; font-size: 12px">
                    {{ __('value') }} </div>
                <div style="display: inline-block">{{ $order->discount->value }}</div>
                <br>
                <div
                    style="display: inline-block; color: gray; width: 200px; font-weight: 400; text-transform: uppercase; font-size: 12px">
                    {{ __('discount value') }} </div>
                <div style="display: inline-block">{{ (new \App\Plugins\Moneys())->setAmount($order->discount->value)->format() }}</div>
                <br>
            @else
                <div style=""> {{ __('value') }} </div>
                <div>{{ $order->discount->value }} %</div>
            @endif
        </section>
    @endif

    @foreach ($order->services as $service)
        <h2 style="font-size: 16px; margin-top:20px"> {{ __('Service') }} </h2>
        <section style="">
            <div
                style="display: inline-block; color: gray; width: 200px; font-weight: 400; text-transform: uppercase; font-size: 12px">
                {{ __('name') }} </div>
            <div style="display: inline-block">{{ $service->name }}</div>
            <br>
            <div
                style="display: inline-block; color: gray; width: 200px; font-weight: 400; text-transform: uppercase; font-size: 12px">
                {{ __('description') }} </div>
            <div style="display: inline-block">{{ $service->description }}</div>
            <br>
            <div
                style="display: inline-block; color: gray; width: 200px; font-weight: 400; text-transform: uppercase; font-size: 12px">
                {{ __('price') }} </div>
            <div style="display: inline-block">{{ $service->price }}</div>
        </section>
    @endforeach

    <h2 style="font-size: 16px; margin-top:20px">{{ __('Products') }}</h2>
    @foreach ($order->items as $item)
        <section style="background-color: #e5e7eb; border-radius: .3rem; padding: .5rem 1rem; margin-top: 20px">
            <!-- product info -->
            @php($priceTable = $item->product['price'])
            <h3 style="">
                <div style="font-size: 16px; display: inline-block;">{{ $item->product['category']['name'] }}</div>
                <span
                    style="font-size: 14px; color: gray; display: inline-block">{{ $item->product['category']['slug'] }}</span>
            </h3>
            <section style="">
                <div
                    style="display: inline-block; color: gray; width: 200px; font-weight: 400; text-transform: uppercase; font-size: 12px">
                    {{ __('quantity') }} </div>
                <div style="display: inline-block">{{ $priceTable['qty'] }}</div>
                <br>
                <div
                    style="display: inline-block; color: gray; width: 200px; font-weight: 400; text-transform: uppercase; font-size: 12px">
                    {{ __('vat') }} </div>
                <div style="display: inline-block">{{ $item->vat ? $item->vat . '%' : '' }}
                </div>
                <br>
                <div
                    style="display: inline-block; color: gray; width: 200px; font-weight: 400; text-transform: uppercase; font-size: 12px">
                    {{ __('status') }} </div>
                <div style="display: inline-block">{{ \App\Enums\Status::getStatusByCode($item->st)?->name }}</div>
                <br>
                <div
                    style="display: inline-block; color: gray; width: 200px; font-weight: 400; text-transform: uppercase; font-size: 12px">
                    {{ __('note') }} </div>
                <div style="display: inline-block">{{ $item->note }}</div>
            </section>

            <h4 style="font-size:14px">{{ __('Product Aspects') }}</h4>
            <div style="">
                @foreach ($item->product['items'] as $object)
                    {{-- <div>{{__('item')}}
    </div> --}}
                    <section style="">
                        <div
                            style="display: inline-block; color: gray; width: 200px; font-weight: 400; text-transform: uppercase; font-size: 12px">
                            {{ getDisplayName($object['key_display_name'], $iso) }}
                        </div>
                        <div style="display: inline-block">{{ getDisplayName($object['value_display_name'], $iso) }}
                        </div>
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
                    <h5 style=""> {{ __('Delivery Addresses') }} </h5>
                    <div>
                        <h4 style=""> {{ __('Delivery Address') }} </h4>
                        <section style="">
                            <div
                                style="display: inline-block; color: gray; width: 200px; font-weight: 400; text-transform: uppercase; font-size: 12px">
                                {{ __('Del Add Company Name') }} </div>
                            <div style="display: inline-block">{{ $address->getoriginal('pivot_company_name') }}
                            </div>
                            <div
                                style="display: inline-block; color: gray; width: 200px; font-weight: 400; text-transform: uppercase; font-size: 12px">
                                {{ __('Del Add Company Contact Person') }}
                            </div>
                            <div style="display: inline-block">{{ $address->getoriginal('pivot_full_name') }}
                            </div>
                            <div
                                style="display: inline-block; color: gray; width: 200px; font-weight: 400; text-transform: uppercase; font-size: 12px">
                                {{ __('Del Address House Number') }} </div>
                            <div style="display: inline-block">{{ trim($address->number) }}</div>
                            <div
                                style="display: inline-block; color: gray; width: 200px; font-weight: 400; text-transform: uppercase; font-size: 12px">
                                {{ __('Del Add Zip Code') }} </div>
                            <div style="display: inline-block">{{ $address->zip_code }}</div>
                            <div
                                style="display: inline-block; color: gray; width: 200px; font-weight: 400; text-transform: uppercase; font-size: 12px">
                                {{ __('Del Add City') }} </div>
                            <div style="display: inline-block">{{ $address->city }}</div>
                            <div
                                style="display: inline-block; color: gray; width: 200px; font-weight: 400; text-transform: uppercase; font-size: 12px">
                                {{ __('Del Add Quantity') }} </div>
                            <div style="display: inline-block">{{ $priceTable['qty'] }}</div>
                        </section>
                    </div>
                @endforeach
            @elseif($item->delivery_separated)
                @foreach ($item->children as $children)
                    <h5 style=""> {{ __('Delivery Addresses') }} </h5>
                    <div>
                        <h4 style=""> {{ __('Delivery Address') }} </h4>
                        <section style="">
                            <div
                                style="display: inline-block; color: gray; width: 200px; font-weight: 400; text-transform: uppercase; font-size: 12px">
                                {{ __('Del Add Company Name') }} </div>
                            <div style="display: inline-block">
                                {{ $children->addresses[0]->getoriginal('pivot_company_name') }}</div>
                            <div
                                style="display: inline-block; color: gray; width: 200px; font-weight: 400; text-transform: uppercase; font-size: 12px">
                                {{ __('Del Add Company Contact Person') }}
                            </div>
                            <div style="display: inline-block">
                                {{ $children->addresses[0]->getoriginal('pivot_full_name') }}</div>
                            <div
                                style="display: inline-block; color: gray; width: 200px; font-weight: 400; text-transform: uppercase; font-size: 12px">
                                {{ __('Del Address House Number') }} </div>
                            <div style="display: inline-block">{{ $children->addresses[0]->number }}</div>
                            <div
                                style="display: inline-block; color: gray; width: 200px; font-weight: 400; text-transform: uppercase; font-size: 12px">
                                {{ __('Del Add Zip Code') }} </div>
                            <div style="display: inline-block">{{ $children->addresses[0]->zip_code }}</div>
                            <div
                                style="display: inline-block; color: gray; width: 200px; font-weight: 400; text-transform: uppercase; font-size: 12px">
                                {{ __('Del Add City') }} </div>
                            <div style="display: inline-block">{{ $children->addresses[0]->city }}</div>
                            <div
                                style="display: inline-block; color: gray; width: 200px; font-weight: 400; text-transform: uppercase; font-size: 12px">
                                {{ __('Del Add Quantity') }} </div>
                            <div style="display: inline-block">{{ $children->qty }}</div>
                        </section>
                    </div>
                @endforeach
            @endif
        </section>

        </section>
    @endforeach

    @if (!$order->delivery_multiple)
        @foreach ($order->address as $address)
            <h5 style=""> {{ __('Delivery Addresses') }} </h5>
            <div>
                <h4 style=""> {{ __('Delivery Address') }} </h4>
                <section style="">
                    <div
                        style="display: inline-block; color: gray; width: 200px; font-weight: 400; text-transform: uppercase; font-size: 12px">
                        {{ __('Del Add Company Name') }} </div>
                    <div style="display: inline-block;">{{ $address->getoriginal('pivot_company_name') }}</div>
                    <br>
                    <div
                        style="display: inline-block; color: gray; width: 200px; font-weight: 400; text-transform: uppercase; font-size: 12px">
                        {{ __('Del Add Company Contact Person') }} </div>
                    <div style="display: inline-block;">{{ $address->getoriginal('pivot_full_name') }}</div>
                    <br>
                    <div
                        style="display: inline-block; color: gray; width: 200px; font-weight: 400; text-transform: uppercase; font-size: 12px">
                        {{ __('Del Address House Number') }} </div>
                    <div style="display: inline-block;">{{ trim($address->number) }}</div>
                    <br>
                    <div
                        style="display: inline-block; color: gray; width: 200px; font-weight: 400; text-transform: uppercase; font-size: 12px">
                        {{ __('Del Add Zip Code') }} </div>
                    <div style="display: inline-block;">{{ $address->zip_code }}</div>
                    <br>
                    <div
                        style="display: inline-block; color: gray; width: 200px; font-weight: 400; text-transform: uppercase; font-size: 12px">
                        {{ __('Del Add City') }} </div>
                    <div style="display: inline-block;">{{ $address->city }}</div>
                </section>
            </div>
        @endforeach
    @endif
</div>
</body>

</html>
