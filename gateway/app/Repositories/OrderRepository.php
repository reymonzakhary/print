<?php


namespace App\Repositories;

use App\Contracts\InitModelAbstract;
use App\Contracts\RepositoryEloquentInterface;
use App\Enums\Status;
use App\Foundation\Settings\Settings;
use App\Models\Tenants\Item;
use App\Models\Tenants\Order;
use App\Models\Tenants\Transaction;
use App\Scoping\Scopes\OrderTypeScope;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Repository
 * @package App\Repositories
 */
final class OrderRepository extends InitModelAbstract implements RepositoryEloquentInterface
{
    /**
     *
     * @param int $per_page
     * @param string $ctx
     * @param bool $member
     * @param array $scopes
     * @return LengthAwarePaginator|Collection
     */
    public function all(
        int $per_page = 10,
        string $ctx = "mgr",
        bool $member = false,
        array $scopes = [],
        string $order_by = 'order_nr',
        string $order_dir = 'desc'
    )
    {
        return $this->model
            ->whereOwnerOrAllowed()
            ->where('orders.type', true)
            ->with([
                'orderedBy' => function ($qs) {
                    return $qs->select(
                        'id', 'email'
                    );
                },
                'lockedBy' => function ($qs) {
                    return $qs->select(
                        'id', 'email'
                    );
                },
                'orderedBy.profile',
                'context' => function ($qs) {
                    return $qs->select(
                        'id', 'name'
                    );
                },
                'address',
                'invoice_address',
                'services',
                'services.media',
                'services.media.tags',
                'team',
                'items',
                'items.media',
                'items.media.tags',
                'items.services',
                'items.services.media',
                'items.services.media.tags',
                'items.addresses',
                'items.children',
                'items.children.addresses',
            ])
            ->withScopes($scopes)
            ->select(
                'orders.id',
                'orders.reference',
                'orders.note',
                'orders.order_nr',
                'orders.discount_id',
                'orders.type',
                'orders.st',
                'orders.st_message',
                'orders.delivery_multiple',
                'orders.delivery_pickup',
                'orders.shipping_cost',
                'orders.ctx_id',
                'orders.team_id',
                'orders.user_id',
                'orders.created_from',
                'orders.properties',
                'orders.created_at',
                'orders.updated_at',
                'orders.expire_at',
                'orders.editing',
                'orders.locked',
                'orders.locked_at',
                'orders.locked_by',
                'orders.message',
                'orders.archived'
            )
            ->orderBy($order_by, $order_dir)
            ->paginate($per_page);
    }

    /**
     * @var string
     */
    private const TYPE = true;

    /**
     * @inheritDoc
     */
    public function show(int $id, bool $type = true): ?Order
    {
        $with = [
            'orderedBy','orderedBy.profile', 'lockedBy', 'context', 'items',
            'items.services', 'items.discount', 'items.media', 'discount',
            'history', 'address', 'invoice_address', 'vat' , 'team','address.country','invoice_address.country',
            'items.media',
            'items.media.tags',
            'items.services',
            'items.addresses',
            'items.children',
            'items.children.addresses' => function ($q) {
                return $q->orderBy('created_at', 'DESC');
            }
        ];

        if (auth()->user()->can('orders-services-list')) {
            $with[] = 'services';
        }

        if (
            $order = $this->model
                ->where('orders.type', $type)
                ->where('orders.id', $id)
                ->with($with)
                ->first()
        ) {
            return $order;
        }
        return null;
    }

    /**
     * @param array $attributes
     * @return Model
     */
    public function create(array $attributes): Order
    {
        return  $this->model->create($attributes);
    }

    /**
     * @inheritDoc
     */
    public function update(int $id, array $attributes): bool
    {
        $order = $attributes['order'];
        $address = collect($order->address)->first();
        unset($attributes['order']);
        if ($attributes['address'] !== null) {
            $order->address()->sync([$attributes['address'] => [
                'type' => $attributes['address_type'],
                'full_name' => $attributes['address_full_name'],
                'company_name' => $attributes['address_company_name'],
                'phone_number' => $attributes['address_phone_number'],
                'tax_nr' => $attributes['address_tax_nr']
            ]]);
        } else {
            $order->address()->detach(optional($address)->id);
        }
        $order->fill($attributes);

        if ($attributes['st'] === 302) {
            collect($order->items()->get())->map(fn($item) => $item->update([
                'st' => 309
            ]));
        }
        if ($attributes['st'] === 300) {
            collect($order->items()->get())->map(fn($item) => $item->update([
                'st' => 300
            ]));
        }
        return $order->save();
    }

    /**
     * @inheritDoc
     */
    public function delete(int $id): bool
    {
        $order = $this->model->where('id', (int)$id)->first();
        if ($order) {
            if ($order->delete()) {
                return true;
            }
        }
        return false;
    }

    /**
     * Get the total number of orders
     *
     * @return int
     */
    public function getTotalOrders(): int
    {
        return $this->model
            ->query()
            ->where('type', true)
            ->count();
    }

    /**
     * Get the next index of a given order
     *
     * @param Order $order
     *
     * @return int|null
     */
    public function getNextIndexOfOrder(Order $order): ?int
    {
        return $this->model
            ->query()
            ->where([
                ['id', '>', $order->getAttribute('id')],
                ['type', '=', 'true']
            ])
            ->min('id');
    }

    /**
     * Get the previous index of a given order
     *
     * @param Order $order
     *
     * @return int|null
     */
    public function getPreviousIndexOfOrder(Order $order): ?int
    {
        return $this->model
            ->query()
            ->where([
                ['id', '<', $order->getAttribute('id')],
                ['type', '=', 'true']
            ])
            ->max('id');
    }

    /**
     * Check if a given transaction is linked (Has Relation) with an order
     *
     * @param Order $order
     * @param Transaction $transaction
     *
     * @return bool
     */
    public function doesOrderOwnTheTransaction(
        Order $order,
        Transaction $transaction
    ): bool
    {
        return $this->model
            ->query()
            ->findOrFail($order->getAttribute('id'))
            ->transactions()
            ->where('transactions.id', $transaction->getAttribute('id'))
            ->exists();
    }

    /**
     * @param Order $order
     *
     * @return Item|null
     */
    public function getItemWithLowestSequenceStatus(
        Order $order,
    ): ?Item
    {
        return $this->model
            ->query()
            ->findOrFail($order->getAttribute('id'))
            ->items()
            ->whereStatusIsNotCancelled()
            ->get()
            ->each(
                static function (Item $item): void {
                    $statusEnum = Status::from($item->getAttribute('st'));

                    $item->setAttribute('st_sequentially_temp', $statusEnum->getSequenceForOrderItem());
                }
            )
            ->sortBy('st_sequentially_temp')
            ->each(
                static function (Item $item): void {
                    $item->discardChanges();
                }
            )
            ->first();
    }

    /**
     * @return array
     */
    protected function scope(): array
    {
        return [
            'type' => new OrderTypeScope()
        ];
    }
}
