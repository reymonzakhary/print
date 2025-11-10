<?php

namespace App\Listeners\Produce;


use App\Events\Produce\SendOrderToProducerEvent;
use App\Events\SendJobTicketToProducerEvent;
use App\Events\Tenant\Order\CreateOrderEvent;
use App\Events\Tenant\Order\Item\FailedProduceItemEvent;
use App\Events\Tenant\Order\Item\ProduceItemEvent;
use App\Events\Tenant\Order\UpdateOrderEvent;
use App\Foundation\Settings\Settings;
use App\Foundation\Status\Status;
use App\Http\Controllers\Tenant\Mgr\Countries\CountryController;
use App\Http\Controllers\Tenant\Mgr\Orders\Items\Addresses\AddressController;
use App\Http\Requests\Addresses\StoreAddressRequest;
use App\Http\Requests\Addresses\StoreItemAddressRequest;
use App\Http\Resources\Items\ItemResource;
use App\Mail\Tenant\Order\ItemProducedMail;
use App\Models\Domain;
use App\Models\Tenant\Company;
use App\Models\Tenant\Country;
use App\Models\Tenant\Item;
use App\Models\Tenant\Order;
use App\Models\Tenant\Setting;
use App\Models\Tenant\User;
use App\Models\Website;
use Carbon\Carbon;
use Exception;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class SendOrderToProducerListener  implements ShouldQueue
{
    use InteractsWithQueue;

    protected $data;

    public function onStoreOrderToProducer($event)
    {
        $current_tenant = $event->tenant_id;
        $supplier_tenant = $event->supplier_id;
        $tenant_hostname = Website::query()->where('uuid', $current_tenant)->with('hostname')->first()->hostname;
        $tenant_user = User::with(['company' , 'profile'])->first();
        $company = Company::main()->first()->addresses->first();
        $producer_domain = Website::query()
            ->where('uuid', $supplier_tenant)
            ->with(['hostname' => function($query) {
                $query->select('fqdn', 'website_id');
            }])
            ->first(['id'])
            ->hostname->fqdn;

        $supplier_website = Website::query()
            ->where('uuid', $supplier_tenant)
            ->with(['hostname' => function($query) {
                $query->select('fqdn', 'website_id');
            }])->first();

        // create validation error
        if (!$company) {
            throw ValidationException::withMessages([
                'invoice_address' => __('The invoice address is required!')
            ]);
        }

        // get whole order.
        $original_order = $event->order;
        // order address
        // check invoice address
        // if order has multiple delivery then check if dlv separated
        $address = $this->getAddress($event->order, collect($event->item)->first(), $company);
        $invoice = ($address instanceof Collection) ? $address->pop() : $address;

        // get only reference
        $order_reference = $original_order->reference;
        ///////// ITEM /////////
        // get whole item
        $items = $event->item;
        // get items files
        $itemsMedia = [];
        $orderMedia = [];

        foreach ($original_order->getMedia() as $media) {
            $orderMedia[] = $media;
        }
        foreach ($items as $item) {
            foreach ($item->getMedia() as $media){
                $itemsMedia[$item->id][] = $media;
            }
        }

        switchSupplier($event->supplier_id);


        $producer_user = User::with('company')->first();
        $producer_company = $producer_user->company()->first();
        $producer_address = $producer_company->addresses()->first();

        $new_order = false;
        $order = Order::query()->with('items')
            ->whereHas('items' , function ($query) use ($original_order) {
                $query->where('product->order_ref' , $original_order->id);
            })
            ->whereDoesntHave('items' , function ($query){
                $query->where('st' , '!=' , Status::NEW);
            })
            ->first();

            if (!$order || ($supplier_website->external && $supplier_website->configure )){
                $producers = [];
                $order = Order::create([
                    'st' => Status::NEW,
                    'type' => true,
                    'ctx_id' => 1,
                    'created_from' => 'system',
                    'delivery_multiple' => $event->order->delivery_multiple, // it is important to make item separated
                    'delivery_pickup' => $event->order->delivery_pickup,
                    'internal' => false,
                    'connection' => $current_tenant,
                    'reference' => $order_reference,
                    'properties' => [
                        'producers' => [],
                        'customer' => $this->getOrderCustomer($event->tenant_id , $tenant_hostname , $tenant_user),
                    ]
                ]);
                if (!empty($orderMedia)){
                    foreach ($orderMedia as $media){
                        $order->addMediaCrossTenant($media , $current_tenant , $supplier_tenant, $order, "/orders/{$order->id}/");
                    }
                }
                $new_order = true;

            }
        if ($new_order){
            event(new CreateOrderEvent($order , User::first()));
        }
        else {
            event(new UpdateOrderEvent($order));
        }

        if (!empty($invoice['invoice'])) {
            $this->attachAddressTo($order, $invoice['invoice'], 'invoice');
        }

        if (!empty($address['delivery'])) {
            $this->attachAddressTo($order, $address['delivery'], 'delivery');
        }

        $producer_item_id = null;

        $is_vat_removed = ($company->country_id !== $order->delivery_address()->first()->country_id && $company->country_id !== $producer_company->addresses->first()->country_id);
        collect($items)->each(function ($item) use ($order, $event, $current_tenant, $address , $itemsMedia , $supplier_tenant , &$producer_item_id , $is_vat_removed) {
           $originalItemId = $item->id;
            $item = Item::create([
                "product" => [
                    ...$item->product->toArray(),
                    'item_ref' => $item->id,
                    'order_ref' => $event->order->id,
                    'order_ref_nr' => $event->order->order_nr,
                ],
                "vat" => $is_vat_removed ? 0 : $item->vat,
                "delivery_separated" => $item->delivery_separated,
                "st" => Status::NEW,
                "supplier_id" => $item->supplier_id,
                "reference" => $item->reference,
                "sku" => $item->sku,
                "supplier_name" => $item->supplier_name,
                "sku_id" => $item->sku_id,
                "connection" => $current_tenant,
                "internal" => false,
            ]);
            if ($is_vat_removed){
                $item->product['price']['vat'] = 0 ;
                $item->product['price']['vat_p'] = 0 ;
                $item->product['price']['vat_ppp'] = 0 ;
            }

            if (!empty($itemsMedia[$originalItemId])) {
                foreach ($itemsMedia[$originalItemId] as $media) {
                    $item->addMediaCrossTenant(
                        media: $media,
                        sourceTenantPath: $current_tenant,
                        targetTenantPath: $supplier_tenant,
                        targetModel: $item,
                        newRelativePath: "/orders/{$order->id}/items/{$item->id}/"
                    );
                }
            }
            $order->items()->save($item, ['qty' => $item->product->quantity]);
            $producer_item_id = $item->id;

        });
        Mail::to($producer_user->email)->send(new ItemProducedMail($producer_company->name , $order->id  , $producer_domain));

        $order->update([
            'type' => false
        ]) && $order->update([
            'type' => true
        ]);

        switchSupplier($event->tenant_id);
        $producers = $event->order->producers ?? [];
        $producersModified = $this->getOrderProducers(
            $event->supplier_id,
            $event,
            $producer_company,
            $producer_address,
            $producer_user,
            $producers
        );
        $original_order->producers = $producersModified;
        $original_order->customer = [];
        $original_order->save();

        $event->item->each(function ($item) use ($event , $tenant_user , $order , $producer_user , $producer_item_id , $is_vat_removed) {
            try {
                if ($is_vat_removed){
                    $item->product['price']['vat'] = 0 ;
                    $item->product['price']['vat_p'] = 0 ;
                    $item->product['price']['vat_ppp'] = 0 ;
                }

                $item->withoutEvents(function () use ($item , $order , $producer_item_id) {
                    $item->update([
                        'st' => Status::IN_PROGRESS,
                        "product" => [
                            ...$item->product->toArray(),
                            'item_ref' => $producer_item_id,
                            'order_ref' => $order->id,
                            'order_ref_nr' => $order->order_nr,
                        ],
                    ]);
                });
                event(new ProduceItemEvent($event->order , $item , $order->id , $tenant_user));
            }catch (Exception $e){
                $item->withoutEvents(function () use ($item , $event , $tenant_user) {
                    $item->update([
                        'st' => Status::NEW,
                    ]);
                });
            }
        });
    }

    /**
     * @param $event
     * @throws \JsonException
     */
    public function onSendToProduce($event)
    {
        /**
         * @todo Send Order to Producer not A items
         */


        // Store data got from tenant
        if ($event->item instanceof Collection) {
            $addresses = [];
            $files = [];
            $items = [];
            foreach ($event->item as $key => $oneItem) {
                $modefiedEvent = [
                    'order' => $event->order,
                    'item' => $oneItem,
                    'iso' => $event->iso,
                    'tenant_id' => $event->tenant_id,
                    'supplier_id' => $event->supplier_id,
                ];
                $this->SendJobTicketToProducer((object)$modefiedEvent);
                $addresses[$oneItem->id] = $this->getAddress($event->order, $oneItem);
                $files[$oneItem->id] = $this->itemMediaList($oneItem);
                $items[$oneItem->id] = collect($oneItem)
                    ->except(['created_at', 'updated_at'])
                    ->merge(["st" => Status::NEW])
                    ->toArray();
            }
        } else {
            $this->SendJobTicketToProducer($event);

            $address = $this->getAddress($event->order, $event->item);
            $file = $this->itemMediaList($event->item);
            $item = collect($event->item)
                ->except(['id', 'created_at', 'updated_at'])
                ->merge(["st" => Status::NEW])
                ->toArray();
        }

        $order = collect($event->order)
            ->only(['type', 'delivery_multiple', 'delivery_pickup'])
            ->merge([
                'st' => Status::NEW,
                'created_from' => 'system',
                'properties' => json_encode([
                    'supplier_id' => $event->tenant_id,
                    'order_id' => $event->order->id,
                    'items' => []
                ], JSON_THROW_ON_ERROR)
            ])->toArray();
        /**
         * switch to Supplier and create order and items
         */

        switchSupplier($event->supplier_id);

        if (Settings::collectExternalOrderByType() === 'order') {

            $orderExists = Order::where('created_at', Carbon::today())->whereJsonContains('properties->order_id', $event->order->id)->first();

            $order = $orderExists ?? Order::create($order);
        } else {
            $order = Order::create($order);
        }

        if (!$event->order->delivery_multiple) {
            $this->attachAddressTo($order, $address);
        }
        if ($event->item instanceof Collection) {
            foreach ($items as $key => $item) {
                $supplierItemId = array_shift($item);
                $item['product'] = json_encode($item['product'], JSON_THROW_ON_ERROR, 512);
                $id = $this->createOrderItem($order, $item, $files[$key], $event, $addresses[$key]);
                $properties = $order->properties;
                $properties->items[] = [
                    "local" => $id->id,
                    "parent" => $supplierItemId
                ];
                $order->properties = json_encode($properties, JSON_THROW_ON_ERROR);
                $order->update(['properties' => json_encode($properties, JSON_THROW_ON_ERROR)]);
            }
        } else {
            $item['product'] = json_encode($item['product'], JSON_THROW_ON_ERROR, 512);
            $id = $this->createOrderItem($order, $item, $file, $event, $address);
            $properties = $order->properties;
            $properties->items[] = [
                'local' => $id->id,
                'parent' => $event->item->id,
            ];
            $order->properties = json_encode($properties, JSON_THROW_ON_ERROR);
            $order->update(['properties' => json_encode($properties, JSON_THROW_ON_ERROR)]);
        }

        switchSupplier($event->tenant_id);
    }


    public function getAddress(
        $order,
        $item,
        $company
    ): mixed
    {

        if ($order->delivery_pickup) {
            return [
                "delivery" => $company,
                "invoice" => $company,
                "company" => $company
            ];
        }

        return match ($order->delivery_multiple) {
            false => [
                "delivery" => $order->address()->first(),
                "invoice" => $company,
                "company" => $company
            ],
            !$item->delivery_separated => call_user_func(function () use ($item, $company) {
                if ($item->delivery_pickup) {
                    return [
                        "delivery" => $company,
                        "invoice" => $company,
                        "company" => $company
                    ];
                }
                return [
                    "delivery" => $item->addresses()->first(),
                    "invoice" => $company,
                    "company" => $company
                ];
            }),
            default => collect($item->children)->map(function ($address) use ($order, $company) {
                if ($address->delivery_pickup) {
                    return [
                        "delivery" => $company,
                        'item' => $address,
                        "invoice" => $company,
                        "company" => $company
                    ];
                }
                return [
                    'delivery' => $address->addresses->first(),
                    'item' => $address,
                    "invoice" => $company,
                    "company" => $company
                ];
            })->add(["invoice" => $company])
        };
    }


    /**
     * @param Order|Item $client
     * @param mixed $address
     * @param string $type
     * @return mixed
     */
    public function attachAddressTo(Order|Item $client, mixed $address, string $type = 'order')
    {
        $method = $client instanceof Order ? "address" : "addresses";
        $request = new StoreAddressRequest(
            $address->only(
                ['address', 'number', 'city', 'region', 'state', 'zip_code', 'country_id']
            )
        );


        $supplierAddress = app(CountryController::class)->store($request, Country::find($request->country_id));

        match ($type) {
            'order' => $client->$method()->syncWithoutDetaching([$supplierAddress->id => [
                'type' => $address?->pivot->type,
                'full_name' => $address?->pivot->full_name,
                'company_name' => $address?->pivot->company_name,
                'phone_number' => $address?->pivot->phone_number,
                'tax_nr' => $address?->pivot->tax_nr
            ]]),
            'invoice' => $client->invoice_address()->sync([$supplierAddress->id => [
                'type' => 'invoice',
                'full_name' => $address->pivot->full_name,
                'company_name' => $address->pivot->company_name,
                'phone_number' => $address->pivot->phone_number,
                'tax_nr' => $address->pivot->tax_nr,
            ]]),
            'delivery' => $client->delivery_address()->sync([$supplierAddress->id => [
                'type' => 'delivery',
                'full_name' => $address->pivot->full_name,
                'company_name' => $address->pivot->company_name,
                'phone_number' => $address->pivot->phone_number,
                'tax_nr' => $address->pivot->tax_nr,
            ]]),
            default => $supplierAddress
        };


        return $supplierAddress;
    }

    /**
     * @param $event ( order, item, iso, tenant_id, supplier_id )
     */
    public function SendJobTicketToProducer($event)
    {
        $supplier = Domain::findByFqdn($event->item->supplier_name)->firstOrFail();
        $xml = View::make('job_tickets.job_ticket_xml', [
            'order' => $event->order,
            'item' => ItemResource::make($event->item),
            'iso' => $event->iso,
            'supplier' => (object)['name' => $supplier->fqdn, 'supplier_id' => $event->supplier_id],
        ])->render();
        switchSupplier($event->supplier_id);

        $this->data = Setting::where('namespace', 'supplier')->pluck('value', 'key');
        $supplierReceiveThrough = Settings::supplierReceiveThrough();
        switchSupplier($event->tenant_id);
        foreach (explode(',', $supplierReceiveThrough) as $method) {
            call_user_func([__CLASS__, 'sendTo' . Str::ucfirst($method)], $xml, $event->order->order_nr, $event->item->id);
        }
    }

    /**
     * @param $file
     * @param $order_id
     * @param $items
     */
    public function sendToMail($file, $order_id, $items)
    {
        try {
            Mail::send('emails.layout', ['key' => 'value'], function ($message) use ($file, $order_id, $items) {
                $message->to(
                    optional($this->data)['supplier_mail_address'],
                    optional($this->data)['supplier_first_name'] . '' . optional($this->data)['supplier_last_name']
                )->subject("order #{$order_id} item #{$items}")
                    ->attachData($file, "JobTicket-Order#{$order_id}-item#{$items}.xml", ["mime" => 'text/xml']);
            });
        } catch (Exception $e) {

        }
    }

    public function sendToFTP($file, $order_id, $items)
    {
        try {
            $ftpConfig = [
                "sftp" => ($this->data['supplier_ftp_connection_type'] === 'sftp') ? true : false,
                "host" => $this->data['supplier_ftp_connection_host'],
                "username" => $this->data['supplier_ftp_connection_username'],
                "password" => $this->data['supplier_ftp_connection_password'],
                "port" => $this->data['supplier_ftp_connection_port'],
                "path" => $this->data['supplier_ftp_connection_path'],
                'driver' => 'ftp',
                'root' => $this->data['supplier_ftp_connection_path'] ?? "/",
                'passive' => false,
                'ignorePassiveAddress' => true,
            ];
            config(['filesystems.disks.ftp' => $ftpConfig]);
            Storage::disk('ftp')->put("JobTicket-Order#{$order_id}-item#{$items}.xml", $file);
        } catch (Exception $e) {
        }
    }

    /**
     * @param $events
     */
    public function subscribe($events)
    {
        $events->listen(
            SendOrderToProducerEvent::class,
            'App\Listeners\Produce\SendOrderToProducerListener@onStoreOrderToProducer'
        );
        $events->listen(
            SendJobTicketToProducerEvent::class,
            'App\Listeners\Produce\SendOrderToProducerListener@SendJobTicketToProducer'
        );

    }

    public function itemMediaList($item)
    {
        return $item->getMedia('items')->toArray();
    }

    /**
     * @param Order $order
     * @param array $item
     * @param mixed $file
     * @param       $event
     * @param mixed $address
     * @return Item
     */
    public function createOrderItem(Order $order, array $item, mixed $files, $event, mixed $address): Item
    {
        $item = $order->items()->create($item);
        foreach ($files as $media) {
            $item->addMediaCrossTenant($order['properties'], $media);
        }

        if ($event->order->delivery_multiple) {

            if (!$item->delivery_separated) {

                $this->attachAddressTo($item, $address);

            } else {

                $data = $address->map(function ($adds) use ($item) {
                    $address = $this->attachAddressTo($item, $adds['delivery']);
                    return [
                        "address" => $address->id,
                        "delivery_pickup" => $adds['item']['delivery_pickup'],
                        "qty" => $adds['item']['qty']
                    ];
                });
                $request = new StoreItemAddressRequest(array_merge($item->toArray(), ['addresses' => $data]));
                app(AddressController::class)->update($order, $item, $request);
            }
        }

        return $item;
    }



    private function getOrderProducers($producer_id , $event , $producer_company, $producer_address , $producer_user , &$producers): array
    {
        if (!isset($producers[$producer_id])) {
            $producers[$producer_id] = [
                 'contract' => [
                     'contract_id' => $event->contract->id,
                     'contract_nr' => $event->contract->contract_nr,
                 ],
                 'connection_id' => $producer_id,
                 'company' => [
                     'name' => $producer_company->name ?? null,
                     'coc' => $producer_company->coc ?? null,
                     'tax_nr' => $producer_company->tax_nr ?? null,
                     'domain' => $producer_company->domain ?? null,
                 ],
                 'address' => $producer_address,
                 'user' => [
                     'id' => $producer_user->id,
                     'first_name' => $producer_user->profile->first_name ?? null,
                     'last_name' => $producer_user->profile->last_name ?? null,
                     'email' => $producer_user->email ?? null,
                 ],
                 'contact_person' => [
                     'id' => $producer_user->id,
                     'first_name' => $producer_user->profile->first_name ?? null,
                     'last_name' => $producer_user->profile->last_name ?? null,
                     'email' => $producer_user->email ?? null,
                 ],
             ];
        }
        return $producers;
    }
    private function getOrderCustomer($tenant_id , $tenant_hostname , $tenant_user): array
    {
        return [
                'connection_id' => $tenant_id,
                'company' => [
                    'name'   => $tenant_hostname->custom_fields->pick('name') ?? null,
                    'coc' => $tenant_hostname->custom_fields->pick('coc') ?? null,
                    'tax_nr' => $tenant_hostname->custom_fields->pick('tax_nr') ?? null,
                    'domain' => $tenant_hostname->custom_fields->pick('domain') ?? null,
                ],
                "email" => $tenant_hostname->custom_fields->pick('email'),
                "profile" => [
                    "avatar" => "https://www.gravatar.com/avatar/d41d8cd98f00b204e9800998ecf8427e?s=45&d=mm",
                    "first_name" => Str::before($tenant_hostname->custom_fields->pick('name'), ' '),
                    "last_name" => Str::after($tenant_hostname->custom_fields->pick('name'), ' '),
                    "gender" => $tenant_hostname->custom_fields->pick('gender'),
                ],
                'user' => [
                    'id' => $tenant_user->id ,
                    'first_name' => $tenant_user->profile->first_name ?? null,
                    'last_name' => $tenant_user->profile->last_name ?? null,
                    'email' => $tenant_user->email ?? null,
                ],
                'contact_person' => [
                    'id' => $tenant_user->id,
                    'first_name' => $tenant_user->profile->first_name ?? null,
                    'last_name' => $tenant_user->profile->last_name ?? null,
                    'email' => $tenant_user->email ?? null,
                ],
        ];
    }

    /**
     * Handle a job failure.
     *
     * @param \App\Events\Produce\SendOrderToProducerEvent|\App\Events\SendJobTicketToProducerEvent $event
     * @param \Throwable $exception
     * @return void
     */
    public function failed($event, $exception)
    {
        switchSupplier($event->tenant_id);
        $tenant_user = User::first();
        $event->item->each(function ($item) use ($event , $tenant_user) {
            $item->withoutEvents(function () use ($item) {
                $item->update(['st' => Status::NEW]);
            });
            event(new FailedProduceItemEvent($event->order , $item , $tenant_user));
        });
    }

}
