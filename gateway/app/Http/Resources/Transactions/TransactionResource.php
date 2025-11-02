<?php

namespace App\Http\Resources\Transactions;

use App\Http\Resources\Discounts\DiscountResource;
use App\Http\Resources\Orders\OrderResource;
use App\Http\Resources\Orders\Transaction\TransactionCustomFieldResource;
use App\Http\Resources\Statuses\StatusResource;
use App\Http\Resources\Teams\TeamResource;
use App\Http\Resources\Users\UserResource;
use App\Plugins\Moneys;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Class TagResource
 * @package App\Http\Resources\Tags
 * @OA\Schema(
 * )
 */
class TransactionResource extends JsonResource
{

    /**
     * @var array
     */
    protected array $withoutFields = [];


    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array
     */
    public function toArray(Request $request): array
    {
        return $this->filterFields([
            'id' => $this->id,

            'order_id' => $this->resource->getAttribute('order_id'),

            'invoice_nr' => $this->invoice_nr,
            'invoice_date' => $this->invoice_date,
            'payment_method' => $this->payment_method,
            'st' => StatusResource::make($this->st),
            'fee' => $this->fee,
            'vat' => $this->vat,

            'discount' => DiscountResource::make($this->whenLoaded('discount')),

            'order_nr' => optional($this->custom_field)['order_nr'],
            'order' => OrderResource::make($this->whenLoaded('order')),

            'price' => $this->resource->getAttribute('custom_field')->pick('total_incl_vat'),

            'display_price' => \moneys()
                ->setAmount(
                    $this->resource->getAttribute('custom_field')->pick('total_incl_vat')
                )
                ->format(),

            'company_id' => $this->company_id,

            'team' => TeamResource::make($this->whenLoaded('team')),
            'user' => UserResource::make($this->whenLoaded('user')),

            'contract_id' => $this->contract_id,
            'type' => $this->type,
            'count' => $this->count,
            'level' => $this->level,

            'parent' => TransactionResource::make($this->whenLoaded('parent')),
            'children' => TransactionResource::collection($this->whenLoaded('children')),

            'due_date' => $this->due_date,
            'expire_at' => $this->expire_at,

            'logs' => $this->whenLoaded('logs'),

            'custom_field' => TransactionCustomFieldResource::make(
                $this->resource->getAttribute('custom_field')
            ),

            'updated_at' => Carbon::parse($this->updated_at)->toDateTimeString(),
            'created_at' => Carbon::parse($this->created_at)->toDateTimeString(),

        ]);
    }

    /**
     * @param mixed $resource
     *
     * @return AnonymousResourceCollection|mixed
     */
    public static function collection(
        $resource
    ): mixed
    {
        return tap(new TransactionResourceCollection($resource), function ($collection) {
            $collection->collects = __CLASS__;
        });
    }

    /**
     * Set the keys that are supposed to be filtered out.
     *
     * @param array $fields
     *
     * @return $this
     */
    public function hide(
        array $fields
    ): self
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
    protected function filterFields(
        array $array
    ): array
    {
        return collect($array)->forget($this->withoutFields)->toArray();
    }
}
