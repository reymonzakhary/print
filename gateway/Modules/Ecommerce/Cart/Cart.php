<?php


namespace Modules\Ecommerce\Cart;


use App\Models\Tenant\User;
use App\Plugins\Moneys;
use JsonException;

class Cart
{

    /**
     * @var Null|User
     */
    private ?User $user;


    /**
     * @var bool
     */
    private bool $changed = false;

    /**
     * Cart constructor.
     * @param null|User $user
     */
    public function __construct(
        ?User $user
    )
    {
        $this->user = $user;
    }

    /**
     * @param $products
     */
    public function add(
        array $products
    )
    {
        $this->user->cart()->syncWithoutDetaching(
            $this->getStorePayload($products)
        );
    }

    /**
     * @param int    $productId
     * @param string $product_type
     * @param array  $variations
     * @param int    $quantity
     * @throws JsonException
     */
    public function update(
        int    $productId,
        string $product_type,
        array  $variations,
        int    $quantity
    )
    {
        $this->user->cart()->updateExistingPivot($productId, [
            'product_type' => $product_type,
            'variations' => json_encode($variations, JSON_THROW_ON_ERROR | JSON_THROW_ON_ERROR),
            'quantity' => $quantity
        ]);
    }

    /**
     * @param int $productId
     */
    public function delete(
        int $productId
    )
    {
        $this->user->cart()->detach($productId);
    }

    /**
     * Clear the user cart
     */
    public function empty()
    {
        $this->user->cart()->detach();
    }

    /**
     * @return bool
     */
    public function isEmpty()
    {
        return $this->user->cart()->sum('quantity') === 0;
    }

    /**
     * @return Moneys
     */
    public function subtotal()
    {
        $subtotal = $this->user->cart->sum(function ($product) {
            return $product->price->amount() * $product->pivot->quantity;
        });

        return (new Moneys())->setAmount($subtotal);
    }

    /**
     * @return Moneys
     */
    public function total()
    {
        return $this->subtotal();
    }

    /**
     * @return mixed
     */
    public function sync()
    {
        return $this->user->cart->each(function ($product) {
            if ($product->stock_product) {
                $quantity = $product->minStock($product->pivot->quantity);
                $this->changed = $quantity !== $product->pivot->quantity;
                $product->pivot->update([
                    'quantity' => $quantity
                ]);
            }
        });
    }

    /**
     * @return bool
     */
    final public function hasChanged(): bool
    {
        return $this->changed;
    }

    /**
     * @param $products
     * @return array
     */
    private function getStorePayload(
        array $products
    ): array
    {

        return collect($products)->keyBy('id')->map(function ($product) {
            return [
                'product_type' => $product['product_type'],
                'variations' => isset($product['variations']) ?
                    json_encode($product['variations'], JSON_THROW_ON_ERROR) :
                    json_encode([], JSON_THROW_ON_ERROR),
                'quantity' => $product['quantity'] + $this->getCurrentQuantity($product['id']),
            ];
        })->toArray();
    }

    /**
     * @param array $products
     * @return array
     */
    private function duplicate(
        array $products
    ): array
    {
        return collect(array_map(function ($product) {
            $collection = [];
            if (count($product['variations']) > 0) {
                foreach ($product['variations'] as $variation) {
                    $collection[] = [
                        'id' => $product['id'],
                        'product_type' => $product['product_type'],
                        'variations' => $variation,
                        'quantity' => $product['quantity'] + $this->getCurrentQuantity($product['id']),
                    ];
                }
            } else {
                $collection[] = [
                    'id' => $product['id'],
                    'product_type' => $product['product_type'],
                    'variations' => NULL,
                    'quantity' => $product['quantity'] + $this->getCurrentQuantity($product['id']),
                ];
            }

            return $collection;
        }, $products))->flatten(1)->toArray();

    }

    /**
     * @param int $productId
     * @return int
     */
    private function getCurrentQuantity(
        int $productId
    ): int
    {
        if ($product = $this->user->cart->where('id', $productId)->first()) {
            return $product->pivot->quantity;
        }
        return 0;
    }
}
