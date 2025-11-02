<?php

namespace App\Http\Resources\Items;

use App\Http\Resources\Address\AddressResource;
use App\Http\Resources\Discounts\DiscountResource;
use App\Http\Resources\Media\MediaResource;
use App\Http\Resources\Quotations\QuotationCustomerResource;
use App\Http\Resources\Statuses\StatusResource;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Class QuotationItemResource
 * @package App\Http\Resources\ItemResource
 * @OA\Schema(
 * )
 */
final class QuotationItemResource extends JsonResource
{
    /**
     * @var array
     */
    protected array $withoutFields = [];

    /**
     * @param mixed $resource
     * @return AnonymousResourceCollection|mixed
     */
    public static function collection($resource): mixed
    {
        return tap(new QuotationItemResourceCollection($resource), function ($collection) {
            $collection->collects = __CLASS__;
        });
    }

    /**
     * Transform the resource into an array.
     * @OA\Property(format="int64", title="id", default="124", description="id", property="id"),
     * @OA\Property(format="int64", title="vat_id", default="null", description="vat_id", property="vat_id"),
     * @OA\Property(format="int64", title="reference", default="null", description="reference", property="reference"),
     * @OA\Property(format="int64", title="discount_type", default="null", description="discount_type", property="discount_type"),
     * @OA\Property(format="int64", title="discount", default="0", description="discount", property="discount"),
     * @OA\Property(type="array", property="status", @OA\Items(ref="#/components/schemas/StatusResource")),
     * @OA\Property(format="string", title="supplier_id", default="d6142bb7-c625-4ab5-add2-d79ba9fc6507", description="supplier_id", property="supplier_id"),
     * @OA\Property(format="string", title="supplier_name", default="reseller.prindustry.test", description="supplier_name", property="supplier_name"),
     * @OA\Property(format="string", title="note", default="", description="note", property="note"),
     * @OA\Property(format="string", title="created_at", default="2022-04-04T09:22:28.000000Z", description="created_at", property="created_at"),
     * @OA\Property(format="string", title="updated_at", default="2022-04-04T09:22:28.000000Z", description="updated_at", property="updated_at"),
     * @OA\Property(format="string", title="qty", default="0", description="qty", property="qty"),
     * @OA\Property(format="bool", title="delivery_separated", default=false, description="delivery_separated", property="delivery_separated"),
     * @OA\Property(format="string", title="delivery_pickup", default="null", description="delivery_pickup", property="delivery_pickup"),
     * @OA\Property(format="string", title="shipping_cost", default="null", description="shipping_cost", property="shipping_cost"),
     * @OA\Property(format="string", title="item_children", default="[]", description="item_children", property="item_children"),
     * @OA\Property(format="string", title="delivery_date", default="06-04-2022", description="delivery_date", property="delivery_date"),
     * @OA\Property(format="string", title="attachments", default="[]", description="attachments", property="attachments"),
     * @OA\Property(format="string", title="delivery_address", default="[]", description="delivery_address", property="delivery_address"),
     *
     * @param Request $request
     * @return array
     */
    public function toArray($request): array
    {

        return $this->filterFields([
            'id' => $this->id,
            'product' => ProductItemResource::make(collect($this->product)->merge(['shipping_cost'=> optional($this->pivot)->shipping_cost??0])),
            'vat' => $this->vat,
            'reference' => $this->reference,
            'discount' => DiscountResource::make($this->whenLoaded('discount'))->hide(
                ['id', 'created_at', 'updated_at']
            ),
            'status' => StatusResource::make($this->st)->hide(
                optional($this->withoutChildrenFields)['status'] ?? ['id', 'created_at', 'updated_at']
            ),
            'supplier_id' => $this->supplier_id,
            'supplier_name' => $this->supplier_name,

            'note' => $this->note,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'qty' => optional($this->pivot)->qty,
            'delivery_separated' => (bool)$this->delivery_separated,
            'delivery_pickup' => optional($this->pivot)->delivery_pickup,
            'shipping_cost' => optional($this->pivot)->shipping_cost,
            'display_shipping_cost' => ((new \App\Plugins\Moneys())->setAmount(optional($this->pivot)->shipping_cost))->format(),

            'item_children' => QuotationItemChildrenResource::collection($this->whenLoaded('children'))->hide([]),
            'delivery_date' => Carbon::now()->addWeekdays($this->deliveryDays)->format('d-m-Y'),
            'attachments' => MediaResource::collection($this->media),
            'delivery_address' => AddressResource::make(collect($this->whenLoaded('addresses'))->first())->hide([]),
            'services' => QuotationItemServiceResource::collection($this->whenLoaded('services')),
            'locked' => $this->locked,
            'locked_by' => QuotationCustomerResource::make($this->whenLoaded('lockedBy'))->hide(
                optional($this->withoutChildrenFields)['lockedBy'] ?? []
            ),
            'locked_at' => $this->locked_at ?? null,
        ]);
    }

    /**
     * Set the keys that are supposed to be filtered out.
     *
     * @param array $fields
     * @return $this
     */
    public function hide(array $fields): static
    {
        $this->withoutFields = $fields;

        return $this;
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
}
