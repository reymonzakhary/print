<?php

declare(strict_types=1);

namespace App\Http\Resources\Orders\Transaction;

use App\Http\Resources\Discounts\DiscountResource;
use App\Http\Resources\Teams\TeamResource;
use App\Http\Resources\Users\UserResource;
use App\Plugins\Moneys;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Resources\Json\JsonResource;

final class TransactionResource extends JsonResource
{
    /**
     * @var array
     */
    protected array $withoutFields = [];

    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return $this->filterFields([
            'id' => $this->resource->getAttribute('id'),

            'order_id' => $this->resource->getAttribute('order_id'),

            'invoice_nr' => $this->resource->getAttribute('invoice_nr'),
            'invoice_date' => $this->resource->getAttribute('invoice_date'),
            'payment_method' => $this->resource->getAttribute('payment_method'),
            'st' => $this->resource->getAttribute('st'),
            'fee' => $this->resource->getAttribute('fee'),
            'vat' => $this->resource->getAttribute('vat'),

            'discount' => DiscountResource::make($this->whenLoaded('discount')),

            'price' => $this->resource->getAttribute('custom_field')->pick('total_incl_vat'),

            'display_price' => \moneys()
                ->setAmount(
                    $this->resource->getAttribute('custom_field')->pick('total_incl_vat')
                )
                ->format(),

            'company_id' => $this->resource->getAttribute('company_id'),

            'team' => TeamResource::make($this->whenLoaded('team')),
            'user' => UserResource::make($this->whenLoaded('user')),

            'contract_id' => $this->resource->getAttribute('contract_id'),
            'type' => $this->resource->getAttribute('type'),
            'count' => $this->resource->getAttribute('count'),
            'level' => $this->resource->getAttribute('level'),

            'parent' => TransactionResource::make($this->whenLoaded('parent')),
            'children' => TransactionResource::collection($this->whenLoaded('children')),

            'due_date' => $this->resource->getAttribute('due_date'),
            'expire_at' => $this->resource->getAttribute('expire_at'),

            'custom_field' => TransactionCustomFieldResource::make(
                $this->resource->getAttribute('custom_field')
            ),

            'updated_at' => $this->resource->getAttribute('updated_at'),
            'created_at' => $this->resource->getAttribute('created_at'),
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
     *
     * @return array
     */
    protected function filterFields(
        array $array
    ): array
    {
        return collect($array)->forget($this->withoutFields)->toArray();
    }
}
