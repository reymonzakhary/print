<?php

namespace Modules\Cms\Foundation\Cart;

use App\Foundation\Status\Status;
use App\Models\Tenant\User;
use Modules\Cms\Foundation\Cart\Contracts\CartContractInterface;
use Illuminate\Http\Request;
use Illuminate\Session\SessionManager;
use App\Models\Tenant\Cart as CartModel;
use App\Models\Tenant\Product;
use App\Models\Tenant\Sku;

class Cart implements CartContractInterface
{
    protected $instance;

    /**
     * @param SessionManager $session
     * @param Request        $request
     */
    public function __construct(
        protected SessionManager $session,
        protected Request        $request
    )
    { }

    /**
     * @param User|null $user
     * @return mixed
     */
    public function exists(
        ?User $user
    ): mixed
    {
        $session = $this->session->has($this->request->tenant->uuid . '_cms_cart_session');
        return match ($session) {
            true => $this->addUserIfNotExists($user),
            default => $session
        };
    }

    /**
     * @param User|null $user
     * @return bool
     */
    protected function addUserIfNotExists(
        ?User $user
    ): bool
    {
        if ($user && !$this->instance()?->user && $this->instance()) {
            $this->instance()->update([
                'user_id' => $user->id
            ]);
        }
        return true;
    }

    /**
     * @return mixed
     */
    protected function instance(): mixed
    {
        if ($this->instance) {
            return $this->instance;
        }
        $this->instance = CartModel::whereUuid($this->session->get(tenant()->uuid . '_cms_cart_session'))->with('media')->first();

        if (!$this->instance) {
            $this->create($this->request->user());
            $this->instance = CartModel::whereUuid($this->session->get(tenant()->uuid . '_cms_cart_session'))->with(['sku.product.category', 'media'])->first();
        }

        return $this->instance;
    }

    /**
     * @return mixed
     */
    public function contents(): mixed
    {
        return $this->instance()->cartVariations;
    }

    /**
     * @param array|null    $variations
     * @param Product|array $product
     * @param int           $quantity
     * @param Sku|null      $sku
     * @param int|string    $product_id
     * @return mixed
     * @throws JsonException
     */
    public function add(
        Product|array $product,
        ?Sku          $sku,
        int|string    $product_id = "",
        ?array        $variations = [],
        int           $quantity = 1,
    )
    {
        if ($sku) {

            $item = $this->instance()->cartVariations()->create([
                'sku_id' => $sku->id,
                'qty' => $quantity,
                'product_id' => $product->row_id,
                'variation' => $variations,
                'st' => Status::NEW,
                'price' => $sku->price->amount()
            ]);
            return $item->id;
        }

        $item = $this->instance()->cartVariations()->create([
            'qty' => $quantity,
            'product_id' => $product_id,
            'variation' => $product,
            'st' => Status::NEW,
            'price' => $sku->price->amount()
        ]);
        return $item->id;

    }

    /**
     * @param array $product
     */
    public function addPrintingProduct(array $product, $qty)
    {
        return $this->instance()
            ->cartVariations()
            ->create([
                'variation' => $product,
                'qty' => $qty,
            ])->id;
    }

    /**
     * @param User|null $user
     */
    public function create(
        ?User $user = null
    ): void
    {
        $instance = CartModel::make();

        if ($user) {
            $instance->user()->associate($user);
        }
        $instance->save();

        $this->session->put($this->request->tenant->uuid . '_cms_cart_session', $instance->uuid);
    }

    /**
     *
     * @return bool
     */
    final public function clean(): bool
    {
        return (bool)$this->instance()->cartVariations()->delete();
    }

    /**
     * @return bool
     */
    public function isEmpty()
    {
        return $this->instance()->cartVariations()->get() === 0;
    }

}
