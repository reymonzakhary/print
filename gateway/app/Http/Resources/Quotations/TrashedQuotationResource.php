<?php

namespace App\Http\Resources\Quotations;

use App\Http\Resources\Address\AddressResource;
use App\Http\Resources\Context\ContextResource;
use App\Http\Resources\Discounts\DiscountResource;
use App\Http\Resources\Items\QuotationItemResource;
use App\Http\Resources\Mails\MailQueueResource;
use App\Http\Resources\Services\QuotationServiceResource;
use App\Http\Resources\Statuses\StatusResource;
use App\Models\Quotation;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Class TrashedQuotationResource
 * @package App\Http\Resources\TrashedQuotationResource
 * @OA\Schema(
 * )
 */
final class TrashedQuotationResource extends JsonResource
{
    /**
     * @var array
     */
    protected array $withoutFields = [];

    /**
     * @var array
     */
    protected array $withoutChildrenFields = [];

    /**
     * Transform the resource into an array.
     * @OA\Property(format="int64", title="id", default="1158", description="ID", property="id"),
     * @OA\Property(format="int64", title="reference", default=" ", description="reference", property="reference"),
     * @OA\Property(format="int64", title="order_nr", default="0001", description="order_nr", property="order_nr"),
     * @OA\Property(format="int64", title="display_price", default="€ 0,00", description="display_price", property="display_price"),
     * @OA\Property(format="int64", title="price", default="1000", description="price", property="price"),
     * @OA\Property(format="date", title="expire_at", default="2022-03-22T14:01:16.000000Z", description="expire_at", property="expire_at"),
     * @OA\Property(format="bool", title="type", default="quotation", description="type", property="type"),
     * @OA\Property(type="array", property="status", @OA\Items(ref="#/components/schemas/StatusResource")),
     * @OA\Property(format="int64", title="delivery_type", default="single", description="delivery_type", property="delivery_type"),
     * @OA\Property(format="int64", title="delivery_method", default="delivery", description="delivery_method", property="delivery_method"),
     * @OA\Property(type="array", property="delivery_address", @OA\Items(ref="#/components/schemas/AddressResource")),
     * @OA\Property(type="array", property="invoice_address", @OA\Items(ref="#/components/schemas/AddressResource")),
     * @OA\Property(format="int64", title="shipping_cost", default="1158", description="shipping_cost", property="shipping_cost"),
     * @OA\Property(type="array", property="context", @OA\Items(ref="#/components/schemas/ContextResource")),
     * @OA\Property(type="array", property="customer", @OA\Items(ref="#/components/schemas/UserResource")),
     * @OA\Property(format="int64", title="created_from", default="mgr", description="created_from", property="created_from"),
     * @OA\Property(format="int64", title="note", default="null", description="note", property="note"),
     * @OA\Property(type="array", property="locked_by", @OA\Items(ref="#/components/schemas/UserResource")),
     * @OA\Property(format="int64", title="locked_at", default="2022-04-04T09:22:04.000000Z", description="locked_at", property="locked_at"),
     * @OA\Property(format="date", title="created_at", default="2022-04-04T09:22:04.000000Z", description="created_at", property="created_at"),
     * @OA\Property(format="date", title="updated_at", default="2022-04-04T09:22:04.000000Z", description="updated_at", property="updated_at"),
     * @OA\Property(format="date", title="deleted_at", default="2022-04-04T09:22:04.000000Z", description="deleted_at", property="deleted_at"),
     * @OA\Property(type="array", property="items", @OA\Items(ref="#/components/schemas/ItemResource")),
     * @OA\Property(format="int64", title="services", default="[]", description="services", property="services"),
     * @param Request $request
     * @return array
     */
    public function toArray(Request $request): array
    {
        $user = $this->whenLoaded('orderedBy');
        $delivery_address = collect($this->whenLoaded('delivery_address'))->first();
        $invoice_address = collect($this->whenLoaded('invoice_address'))->first();
        $external_quotation_id = null;

        if ($this->connection === 'cec') {
            $user = User::find($this->user_id)->load('profile', 'companies', 'companies.addresses');
            $company = collect($user->companies)->first();
            $external_quotation_id = Quotation::where([
                ['internal_id', $this->id],
                ['hostname_id', $request->hostname->id],
            ])->first()?->external_id;
            $delivery_address = collect($company?->addresses)->first();
            $invoice_address = collect($company?->addresses)->first();
        }

        return $this->filterFields([
            'id' => $this->id,
            'external_id' => $external_quotation_id,
            'reference' => $this->reference,
            'order_nr' => $this->order_nr,
            'display_price' => $this->price->format(),
            'price' => $this->price->amount(),
            'expire_at' => $this->expire_at,
            'type' => config('tenant_order_config.type')[$this->type],
            'status' => StatusResource::make($this->st),
            'delivery_type' => config('tenant_order_config.delivery_type')[(boolean)$this->delivery_multiple],
            'delivery_method' =>
                (boolean)$this->delivery_multiple ?
                    null :
                    config('tenant_order_config.delivery_method')
                    [$this->delivery_pickup ?? 0]
            ,
            'delivery_address' => AddressResource::make($delivery_address)->hide(
                optional($this->withoutChildrenFields)['address'] ?? []
            ),
            'invoice_address' => AddressResource::make($invoice_address)->hide(
                optional($this->withoutChildrenFields)['address'] ?? []
            ),
            'shipping_cost' => $this->shipping_cost,
            'display_shipping_cost' => ((new \App\Plugins\Moneys())->setAmount($this->shipping_cost))->format(),
            'context' => ContextResource::make($this->whenLoaded('context'))->hide(
                optional($this->withoutChildrenFields)['context'] ?? []
            ),
            'external_connection' => (bool)$this->connection,
            'customer' => QuotationCustomerResource::make($user)->hide(
                optional($this->withoutChildrenFields)['customer'] ?? []
            ),
            'created_from' => $this->created_from,
            'note' => $this->note,
            'message' => $this->message,
            'locked_by' => QuotationCustomerResource::make($this->whenLoaded('lockedBy'))->hide(
                optional($this->withoutChildrenFields)['lockedBy'] ?? []
            ),
            'locked_at' => $this->locked_at,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'deleted_at' => $this->deleted_at,
            'items' => QuotationItemResource::collection($this->whenLoaded('items'))->hide(
                optional($this->withoutChildrenFields)['items'] ?? []
            ),
            'services' => QuotationServiceResource::collection($this->services),
            'discount' => DiscountResource::make($this->whenLoaded('discount'))->hide(
                ['id', 'created_at', 'updated_at']
            ),

            'total_price' => optional($this)->total_price,
            'display_total_price' => ((new \App\Plugins\Moneys())->setAmount(optional($this)->total_price))->format(),
            'subTotal_price' => optional($this)->subTotal_price,
            'display_subTotal_price' => ((new \App\Plugins\Moneys())->setAmount(optional($this)->subTotal_price))->format(),
            'services_price' => optional($this)->services_price,
            'display_services_price' => ((new \App\Plugins\Moneys())->setAmount(optional($this)->services_price))->format(),
            'vat_price' => optional($this)->vats_price,
            'display_vat_price' => ((new \App\Plugins\Moneys())->setAmount(optional($this)->vats_price))->format(),
            'items_price_array' => optional($this)->items_price_array,
            'author' => QuotationCustomerResource::make($this->whenLoaded('author'))->hide(
                optional($this->withoutChildrenFields)['author'] ?? []
            ),
            'mails' => MailQueueResource::collection($this->whenLoaded('mailQueues'))
        ]);
    }

    /**
     * Remove the filtered keys.
     *
     * @param array $array
     * @return array
     */
    protected function filterFields(array $array): array
    {
        return collect($array)->forget($this->withoutFields)->toArray();
    }

    /**
     * Set the keys that are supposed to be filtered out.
     *
     * @param array $fields
     * @return $this
     */
    final public function hide(array $fields): self
    {
        $this->withoutFields = $fields;

        return $this;
    }

    /**
     * @param mixed $resource
     * @return AnonymousResourceCollection|mixed
     */
    public static function collection(mixed $resource): mixed
    {
        return tap(new QuotationResourceCollection($resource), function ($collection) {
            $collection->collects = __CLASS__;
        });
    }

    /**
     * Set the keys that are supposed to be filtered out from children tables.
     * @param array $fields
     * @return $this
     */
    final public function hideChildren(array $fields): self
    {
        $this->withoutChildrenFields = $fields;
        return $this;
    }
}
