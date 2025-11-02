@if ($order->orderedBy->profile->first_name)
    <p>first name : {{$order->orderedBy->profile->first_name}}</p>
    <p>last name : {{$order->orderedBy->profile->last_name}}</p>
@else
    <p>Name: {{$order->orderedBy->username}}</p>

@endif

<table>
    <thead>
    <tr>
        <th scope="col">#</th>
        <th scope="col">product</th>
        <th scope="col">price per pice</th>
        <th scope="col">delivery after</th>
        <th scope="col">qty</th>
    </tr>
    </thead>
    <tbody>
    @foreach($order->items as $key => $item)
        @php
            $product = optional(json_decode($item->product))

        @endphp
        <tr>
            <th scope="row">{{$product->collection}}</th>
            <td>{{$product->category_name}}</td>
            <td>{{$product->prices->tables->ppp}}</td>
            <td>{{$product->prices->tables->dlv->days}}</td>
            <td>{{$product->prices->tables->qty}}</td>
        </tr>
    @endforeach
    </tbody>
</table>

<p>total : € {{$product->prices->tables->p}} </p>
<p>This quotation will expire after : {{$order->expire_at->diffForHumans()}} </p>

<a class="btn btn-primary" href="{{url('api/v1/mgr/orders/'.$order->id.'/notification/acceptance')}}">Accept Offer</a>
