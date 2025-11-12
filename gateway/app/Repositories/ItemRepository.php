<?php


namespace App\Repositories;

use App\Contracts\InitModelAbstract;
use App\Contracts\RepositoryEloquentInterface;
use App\Enums\Status;
use App\Events\Tenant\Order\CreatedItemOrderEvent;
use App\Events\Tenant\Order\Item\ChangeItemStatusEvent;
use App\Events\Tenant\Order\RemoveItemOrderEvent;
use App\Events\Tenant\Order\UpdateItemOrderEvent;
use App\Facades\Settings;
use App\Mail\Tenant\Order\ProducedItemStatusChangedMail;
use App\Models\Tenant\Item;
use App\Models\Tenant\Order;
use App\Models\Website;
use App\Plugins\Moneys;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Mail;

/**
 * Class Repostitory
 * @package App\Repositories
 */
class ItemRepository extends InitModelAbstract implements RepositoryEloquentInterface
{

    public $order = null;

    /**
     * @inheritDoc
     */
    public function show(int $id): ?Model
    {
        if ($item = $this->order->items()->where('items.id', $id)->with('services', 'discount', 'children', 'media', 'sk.product')->first()) {
            return $item;
        }
        return null;
    }

    /**
     * @inheritDoc
     */
    public function all(int $per_page = 10): Collection
    {
        return $this->order->items()
            ->with('services', 'discount', 'children.addresses', 'media', 'sk.product')
            ->orderBy('created_at', 'desc')->get();
    }

    /**
     * @inheritDoc
     */
    public function create(array $attributes): Model
    {
        $user = request()->user();
        $qty = optional($attributes['price'])['qty'];
        $shippingCost = optional($attributes)['shipping_cost'];

        $item = $this->model->create([
            "product" => [
                "type" => optional($attributes)['type'],
                "divided" => optional($attributes)['divided'],
                "calculation_type" => optional($attributes)['calculation_type'],
                "connection" => optional($attributes)['connection'],
                "tenant_id" => optional($attributes)['tenant_id'],
                "tenant_name" => optional($attributes)['tenant_name'],
                "external" => optional($attributes)['external'],
                "external_id" => optional($attributes)['external_id'],
                "external_name" => optional($attributes)['external_name'],
                "items" => optional($attributes)['items'],
                "product" => optional($attributes)['product'],
                "category" => optional($attributes)['category'],
                "margins" => optional($attributes)['margins'],
                "quantity" => optional($attributes)['quantity'],
                "calculation" => optional($attributes)['calculation'],
                "price" => optional($attributes)['price']
            ],
            "reference" => optional($attributes)['reference'],
            "note" => optional($attributes)['note'],
            "shipping_cost" => optional($attributes)['shipping_cost'],
            "vat" => optional($attributes)['vat']??Settings::vat()?->value,
            "supplier_name" => optional($attributes)['supplier_name'],
            "supplier_id" => optional($attributes)['supplier_id'],
            "delivery_separated" => optional($attributes)['delivery_separated'],
            "connection" => optional($attributes)['connection'],
            "internal" => optional($attributes)['connection'] === tenant()->uuid
        ]);

        $this->order->items()->save($item, ['qty' => $qty, 'shipping_cost' => $shippingCost]);

        event(new CreatedItemOrderEvent($this->order, $item, $user));

        return $item;
    }

    /**
     * @inheritDoc
     */
    public function update(int $id, array $attributes): bool
    {
        if ($item = $this->order->items()->where('items.id', $id)->first()) {
            if (isset($attributes['product'])) {
                $vat = $item->vat;
                $shippingCost = optional($attributes)['shipping_cost'] ?? 0;
                $qty = optional(optional($attributes)['product']['price'])['qty'] ?? optional($attributes)['product']['quantity'];
                $attributes['supplier_id'] ??= $attributes['product']['external_id'];
                $attributes['supplier_name'] ??= $attributes['product']['external_name'];
                // check the vat
                if(isset($attributes['price'])){
                    $qty = optional(optional($attributes)['product']['price'])['qty'] ?? optional($attributes)['product']['quantity'];
                    $attributes['product']['price'] = optional($attributes)['price'];
                    $vat = $attributes['product']['price']['vat'];
                }
                $attributes = array_merge(['qty' => $qty], $attributes);
                $this->order->items()->updateExistingPivot($item, [
                    'qty' =>$qty,
                    'shipping_cost' => $shippingCost,
                    'vat' => $vat,
                    'delivery_pickup' => optional($attributes)['delivery_pickup']?:false,
                ]);
            }

            $user = auth()->user();

            $item->fill($attributes);

            event(new UpdateItemOrderEvent($this->order, $item, $item->getOriginal(), $item->getAttributes(), $user));

            if (!$item->internal && optional($item->product)->order_ref){
                $this->handleExternalItemUpdate($item);
            }


            $currentTenant = tenant()->uuid;
            if (
                $currentTenant !== $item->supplier_id &&
                in_array($item->st, [Status::DRAFT->value, Status::PENDING->value, Status::NEW->value]) &&
                !optional($item->product)->internal_margin_apllied &&
                !$item->product->price['margins']
            ) {
                $margin = Moneys::getMargin(
                    optional($attributes)['qty'] ?? optional($item)['qty'],
                    $currentTenant);

                if (count(
                    array_diff_assoc(
                        $margin ,
                        $item->product->price['margins'] ?? []
                        )
                )) {
                    $item['product']['price'] = Moneys::applyMyMargin(
                        $item['product']['price'],
                        $margin
                    );
                    $item->product->internal_margin_apllied = true;
                }
            }

            if ($item->save()) {
                if (isset($attributes['delivery_separated'])) {
                    if ($attributes['delivery_separated']) {
                        $item->addresses()->detach();
                    } else {
                        collect($item->children()->get())->map(function ($address) {
                            $address->addresses()->detach();
                            $address->delete();
                        });
                    }
                }
            }

            return true;
        }

        return false;
    }

    /**
     * @inheritDoc
     */
    public function delete(int $id): bool
    {
        $user = auth()->user();
        $item = $this->model->where('id', (int)$id)->first();
        if ($item) {
            event(new RemoveItemOrderEvent($this->order, $item, $user));
            return $item->delete();
        }
        return false;
    }


    private function handleExternalItemUpdate(Item $item)
    {
        $currentTenant = tenant()->uuid;
        switchSupplier($item->connection);
        if (optional($item->product)->order_ref) {
            $order = Order::firstWhere('id' , $item->product->order_ref);
            event(new ChangeItemStatusEvent($order , $item->product->item_ref, $item->product->order_ref , $this->order->customer , is_external: true));
            $tenantDomain = Website::query()->where('uuid', $item->connection)->with('hostname')->first()->hostname->fqdn;
            if (isset($this->order->customer['email'])){
                Mail::to($this->order->customer['email'])
                    ->queue(new ProducedItemStatusChangedMail($this->order->customer['company']['name'] , $order->id , $tenantDomain ,
                        $item->product->item_ref , strtolower(Status::from($item->st)->name)));
            }
        }
        switchSupplier($currentTenant);
    }
}
