<?php

echo "<?xml version='1.0' standalone='yes'?>";
?>
<order id="{{$order->order_nr}}">
    <createdAt>{{$order->created_at}}</createdAt>
    <contextName>{{$order->context->name}}</contextName>
    <contextId>{{$order->context->id}}</contextId>

    <!-- order customer data -->
    <customer id="{{$order->orderedBy?->id}}">
        <name>{{$order->orderedBy?->profile->first_name}} {{$order->orderedBy?->profile->last_name}}</name>
        <email>{{$order->orderedBy?->email}}</email>
        @if($order->orderedBy?->invoiceAddress())
            <invoiceAddress>
                <address>{{$order->orderedBy->invoiceAddress()->address}}</address>
                <number>{{$order->orderedBy->invoiceAddress()->number}}</number>
                <city>{{$order->orderedBy->invoiceAddress()->city}}</city>
                <region>{{$order->orderedBy->invoiceAddress()->region}}</region>
                <zipCode>{{$order->orderedBy->invoiceAddress()->zip_code}}</zipCode>
                <phoneNumber>{{$order->orderedBy->invoiceAddress()->pivot->phone_number}}</phoneNumber>
            </invoiceAddress>
        @endif
    </customer>


    <!-- invoice info -->
    <orderNumber>{{$order->order_nr}}</orderNumber>
    <Invoice>{{__("INVOICE")}}</Invoice>
    <status>{{ \App\Enums\Status::getStatusByCode($order->st)?->name }}</status>
    <shippingCost>{{ $order->shipping_cost / 100 }}</shippingCost>
    <note>{{ $order->note }}</note>
    <price>{{ $order->price }}</price>
    <deliveryMultiple>{{ $order->delivery_multiple? "true": "false" }}</deliveryMultiple>
    <deliveryPickup>{{ $order->delivery_pickup? "true": "false" }}</deliveryPickup>

    @if ($order->discount)
    <discount>
        <type>{{$order->discount->type}}</type>
        @if ($order->discount->type == 'fixed')
            <value>{{$order->discount->value}}</value>
            <discountValue>{{ (new \App\Plugins\Moneys())->setAmount($order->discount->value)->format() }}</discountValue>
        @else
            <value>{{$order->discount->value}} %</value>
        @endif
    </discount>
    @endif

    @foreach ($order->services as $service)
        <service>
            <name>{{ $service->name }}</name>
            <description>{{ $service->description }}</description>
            <price>{{ $service->price }}</price>
        </service>
    @endforeach

    <Products>
        @foreach ($order->items as $item)
            <!-- product info -->
            @php ($priceTable = $item->product['price'])
            <Product>
                <producerName>{{optional($item)->supplier_name ?? optional(optional($item)->product)->tenant_name}}</producerName>
                <producerId>{{optional($item)->supplier_id}}</producerId>

                <qty>{{ $priceTable['qty'] }}</qty>
                <vat>{{ $item->vat? $item->vat .  '%' : '' }}</vat>
                <status>{{ \App\Enums\Status::getStatusByCode($item->st)?->name }}</status>
                <note>{{ $item->note }}</note>

                <!-- product Aspects -->
                <ProductAspects>
                    @foreach($item->product['items'] as $object)
                        <item>
                            <box>{{getDisplayName($object['key_display_name'], $iso)}}</box>
                            <option>{{getDisplayName($object['value_display_name'], $iso)}}</option>
                        </item>
                    @endforeach
                </ProductAspects>

                <!-- files attached to item -->
                <Files>
                    @foreach ($item->getMedia('items') as $media)
                        <File>
                            <Url>{{$media->getPublicFileUrl('tenant', $media->path, $media->name)}}</Url>
                        </File>
                    @endforeach
                </Files>

                <!-- product category -->
                <categoryName>{{$item->product['category']['name']}}</categoryName>
                <categorySlug>{{$item->product['category']['slug']}}</categorySlug>

                <deliverySeparated>{{ $item->delivery_separated? "true": "false" }}</deliverySeparated>

                @if(!$item->delivery_separated)
                    @foreach($item->addresses as $address)
                        <DeliveryAddresses>
                            <DeliveryAddress>

                                <DelAddCompanyName>{{$address->getoriginal("pivot_company_name")}}</DelAddCompanyName>
                                <DelAddCompanyContactPerson>{{$address->getoriginal("pivot_full_name")}}</DelAddCompanyContactPerson>
                                <DelAddressHouseNr>{{trim($address->number)}}</DelAddressHouseNr>
                                <DelAddZipCode>{{$address->zip_code}}</DelAddZipCode>
                                <DelAddCity>{{$address->city}}</DelAddCity>
                                <DelAddQuantity>{{$priceTable['qty']}}</DelAddQuantity>
                            </DeliveryAddress>
                        </DeliveryAddresses>
                    @endforeach
                @else
                    @foreach($item->children as $children)
                        <DeliveryAddresses>
                            <DeliveryAddress>
                                <DelAddCompanyName>{{$children->addresses[0]->getoriginal("pivot_company_name")}}</DelAddCompanyName>
                                <DelAddCompanyContactPerson>{{$children->addresses[0]->getoriginal("pivot_full_name")}}</DelAddCompanyContactPerson>
                                <DelAddressHouseNr>{{$children->addresses[0]->number}}</DelAddressHouseNr>
                                <DelAddZipCode>{{$children->addresses[0]->zip_code}}</DelAddZipCode>
                                <DelAddCity>{{$children->addresses[0]->city}}</DelAddCity>
                                <DelAddQuantity>{{$children->qty}}</DelAddQuantity>
                            </DeliveryAddress>
                        </DeliveryAddresses>
                    @endforeach
                @endif
            </Product>
        @endforeach
    </Products>

    @if(!$order->delivery_multiple)
        @foreach($order->address as $address)
            <DeliveryAddresses>
                <DeliveryAddress>
                    <DelAddCompanyName>{{$address->getoriginal("pivot_company_name")}}</DelAddCompanyName>
                    <DelAddCompanyContactPerson>{{$address->getoriginal("pivot_full_name")}}</DelAddCompanyContactPerson>
                    <DelAddressHouseNr>{{trim($address->number)}}</DelAddressHouseNr>
                    <DelAddZipCode>{{$address->zip_code}}</DelAddZipCode>
                    <DelAddCity>{{$address->city}}</DelAddCity>
                </DeliveryAddress>
            </DeliveryAddresses>
        @endforeach
    @endif
</order>
