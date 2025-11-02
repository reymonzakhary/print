<?php

namespace App\Cart;

use App\Blueprints\Contracts\BlueprintContactInterface;
use App\Cart\Contracts\CartContractInterface;
use App\Enums\Status as EnumsStatus;
use App\Facades\Settings;
use App\Foundation\Status\Status;
use App\Models\Tenants\Cart as CartModel;
use App\Models\Tenants\CartVariation;
use App\Models\Tenants\Product;
use App\Models\Tenants\Sku;
use App\Models\Tenants\User;
use App\Plugins\Moneys;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Http\Request;
use Illuminate\Session\SessionManager;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use JsonException;
use Throwable;

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
    {
    }

    /**
     * @param User|null $user
     * @return mixed
     */
    public function exists(
        ?User $user
    ): mixed
    {
        $tenant = $this->request->tenant ?? null;
        if (!$tenant || !$tenant->uuid) {
            throw new \RuntimeException("Tenant is not resolved. Cannot access cart session.");
        }
        $session = $this->session->has($tenant->uuid . '_cart_session');
        return match ($session) {
            true => $this->addUserIfNotExists($user),
            default => $session
        };
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

        $this->session->put($this->request->tenant->uuid . '_cart_session', $instance->uuid);

    }


    /**
     * @return mixed
     */
    public function contents(): mixed
    {
        return $this->instance()->cartVariations;
    }

    /**
     * @return mixed
     */
    public function contentCount(): mixed
    {
        return $this->contents()->count();
    }

    /**
     * @param BlueprintContactInterface $blueprint
     * @param Product|string            $product
     * @param int                       $quantity
     * @param string|null               $ns
     * @param array|null                $variations
     * @param string                    $mode
     * @param Sku|null                  $sku
     * @param array|null                $template
     * @param string|null               $type
     * @param array|null                $files
     * @return mixed|void
     * @throws BindingResolutionException
     * @throws ValidationException|Throwable
     */
    public function apply(
        BlueprintContactInterface $blueprint,
        Product|string            $product,
        int                       $quantity,
        ?string                   $ns,
        ?array                    $variations,
        string                    $mode,
        ?Sku                      $sku,
        ?array                    $template,
        ?string                   $type,
        ?array                    $files = []
    )
    {

        if ($sku) {
            if ($ns === 'shop' && $product->hasBlueprint) {

                $this->request->merge([
                    'product' => $product,
                    'quantity' => $quantity,
                    'ns' => $ns,
                    'variations' => $variations,
                    'mode' => $mode,
                    'sku' => $sku,
                    'template' => $template,
                    'type' => $type,
                    'files' => $files,
                    'child' => false,
                    'attachment_to' => 'self',
                    'attachment_type' => 'output',
                    'attachment_destination' => 'request',
                ]);
                return $blueprint->init($this->request)->run()->get('output');
            }

            $item = $this->instance()->cartVariations()->create([
                'sku_id' => $sku->id,
                'qty' => $quantity,
                'product_id' => $product->row_id,
                'variation' => $variations,
                'st' => Status::NEW,
                'price' => $sku->price->multiply(100)->amount()
            ]);

            if ($product->hasBlueprint) {

                $this->request->merge([
                    'product' => $product,
                    'quantity' => $quantity,
                    'ns' => $ns,
                    'variations' => $variations,
                    'mode' => $mode,
                    'sku' => $sku,
                    'template' => $template,
                    'type' => $type,
                    'files' => $files,
                    'child' => false,
                    'attachment_to' => 'cart_item',
                    'attachment_type' => 'output',
                    'attachment_destination' => $item,
                    'status_from' => Status::PENDING,
                    'status_to' => Status::NEW,
                ]);

                $blueprint->init($this->request)->runAsPreferred();
            }

            return $item;
        }

    }

    /**
     * @param array $request
     * @param int $qty
     * @param float $price
     * @param array $files
     * @return mixed
     */
    public function addPrintingProduct(
        array $request,
        int $qty,
        float $price,
        array $files = []
    ): mixed
    {
        return DB::transaction(function () use ($request, $price, $qty, $files) {
            $cartVariation = $this->instance()
                ->cartVariations()
                ->create([
                    'variation' => $request,
                    'price' => $price,
                    'st' => EnumsStatus::NEW->value,
                    'qty' => $qty,
                ]);

            if ($files) {
                $path = "/{$this->instance->id}/items/{$cartVariation->id}";
                $cartVariation->addMedias(
                    $files,
                    path: $path,
                    disk: 'carts',
                );
            }

            return $cartVariation;
        });

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

//            if ($variation = $this->getSkus($sku)) {
//                $blueprint = $product->blueprint->first(fn($i)=>$i->ns !== 'production');
//                $UpdateQuantityAction = collect(optional($blueprint)->configuration)->filter(fn($i)=> $i['data']['actions']['model']==='UpdateQuantityAction')->count();
//                $quantity = !$UpdateQuantityAction ? $quantity + $variation->pivot->qty : $variation->pivot->qty;
//            }
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


    public function getSkus(Sku $sku)
    {
        return $this->instance()?->skus?->find($sku->id);
    }

    /**
     * @return mixed
     */
    protected function instance(): mixed
    {
        if ($this->instance) {
            return $this->instance;
        }

        if (!tenant()?->uuid) {
            return null;
        }
        $this->instance = CartModel::whereUuid($this->session->get(tenant()?->uuid . '_cart_session'))
            ->with('media', 'cartVariations.media')
            ->first();

        if (!$this->instance) {
            $this->create($this->request->user());
            $this->instance = CartModel::whereUuid($this->session->get(tenant()?->uuid . '_cart_session'))
                ->with('media', 'cartVariations.media')
                ->first();
        }

        return $this->instance;
    }

    /**
     *
     * @return int
     */
    public function id():int
    {
        return $this->instance()->id;
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


//    /**
//     * @var Null|User
//     */
//    private ?User $user;
//
//    /**
//     * @var bool
//     */
    private bool $changed = false;
//
//    /**
//     * Cart constructor.
//     * @param null|User $user
//     */
//    public function  __construct(
//        ?User $user
//    )
//    {
//        $this->user = $user;
//    }
//
//    public function products()
//    {
//        return $this->user->cart;
//    }
//
//    /**
//     * @param array $products
//     */
//    public function add(
//        array $products
//    )
//    {
//        collect($this->getStorePayload($products))->map(function($product) {
//            if($product) {
//                $this->user->cart()->attach([
//                    $product['product'] => [
//                        'product_type' => $product['product_type'],
//                        'variations' => $product['variations'],
//                        'quantity' => $product['quantity']
//                    ]
//                ]);
//            }
//        });
//    }
//
//    /**
//     * @param int $productId
//     * @param string $product_type
//     * @param array $variations
//     * @param int $quantity
//     * @throws \JsonException
//     */
//    public function update(
//        int $productId,
//        string $product_type,
//        array $variations,
//        int $quantity
//    )
//    {
//        $this->user->cart()->updateExistingPivot($productId, [
//            'product_type' => $product_type,
//            'variations' => json_encode($variations, JSON_THROW_ON_ERROR | JSON_THROW_ON_ERROR),
//            'quantity' => $quantity
//        ]);
//    }
//
//    /**
//     * @param int $productId
//     */
//    public function delete(
//        int $productId
//    )
//    {
//        $this->user->cart()->detach($productId);
//    }
//
//    /**
//     * Clear the user cart
//     */
    public function empty()
    {
        $this->instance()->cart()->detach();
    }
//

    /**
     * @return bool
     */
    public function isEmpty()
    {
//        return $this->instance()->cartVariations()->get()->sum('qty') === 0;
        return $this->instance()->cartVariations()->get() === 0;
    }
    /**
     * @return Moneys
     */
    public function subtotal(): Moneys
    {
        $subtotal = $this->instance()->cartVariations()->get()->sum(function($product) {
              return $product->sku? $product->price->multiply(100)->amount() * $product->qty: $product->variation['price']['selling_price_ex'];
        });

        return (new Moneys())->setAmount($subtotal);
    }

    /**
     * @return Moneys
     */
    public function vat(): Moneys
    {
        $vat = $this->instance()->cartVariations()->get()->sum(function($product) {
            return  $product->sku?  $product->price
                ->multiply(100)
                ->setTax(Settings::vat()->value)
                ->multiply($product->qty)->amount(false,true):
                optional(optional($product->variation)['price'])['vat_p'];
        });
        return \moneys()->setAmount($vat);
    }


    public function total(): Moneys
    {
        $vat = $this->vat()->amount();
        return  $this->subtotal()->add($vat);
    }

    /**
     * @return mixed
     */
    public function sync()
    {
        return $this->instance()->cartVariations()->get()->each(function($product) {
            if($product->stock_product) {
                $quantity = $product->minStock($product->qty);
                $this->changed = $quantity !== $product->qty;
                $product->pivot->update([
                    'quantity' => $quantity
                ]);
            }
        });
    }
//
    /**
     * @return bool
     */
    final public function hasChanged(): bool
    {
        return $this->changed;
    }
//
//    /**
//     * @param array $products
//     * @return array
//     */
//    private function getStorePayload(
//        array $products
//    ): array
//    {
//        return  collect($products)->map(function($product) {
//            $cart = UserCart::
//                where('user_id' , $this->user->id)
//                ->where('product_id', $product['id'])
//                ->where('product_type', $product['product_type'])
//                ->where('variations', json_encode($product['variations'], JSON_THROW_ON_ERROR))
//            ->first();
//            if(!$cart){
//                return [
//                    'product' => $product['id'],
//                    'product_type' => $product['product_type'],
//                    'variations' => isset($product['variations'])?
//                        json_encode($product['variations'], JSON_THROW_ON_ERROR):
//                        json_encode([], JSON_THROW_ON_ERROR),
//                    'quantity' => $product['quantity'],
//                ];
//            }else{
//                $cart->update([
//                    'product_type' => $product['product_type'],
//                    'quantity' =>$cart->quantity+$product['quantity']
//                ]);
//            }
//        })->toArray();
//    }
//
//    /**
//     * @param array $products
//     * @return array
//     */
//    private function duplicate(
//        array $products
//    ): array
//    {
//        return collect(array_map(function($product) {
//            $collection = [];
//            if(count($product['variations'])>0){
//                foreach($product['variations'] as $variation) {
//                    $collection[] = [
//                        'id' => $product['id'],
//                        'product_type' => $product['product_type'],
//                        'variations' => $variation,
//                        'quantity' => $product['quantity']+$this->getCurrentQuantity($product['id'],$product['variations']),
//                    ];
//                }
//            }else{
//                $collection[] =  [
//                    'id' => $product['id'],
//                    'product_type' => $product['product_type'],
//                    'variations' => NULL,
//                    'quantity' => $product['quantity']+$this->getCurrentQuantity($product['id'], $product['variation']),
//                ];
//            }
//
//            return $collection;
//        },$products))->flatten(1)->toArray();
//
//    }
//
//    /**
//     * @param int $productId
//     * @param array $variation
//     * @return int
//     */
//    private function getCurrentQuantity(
//        int $productId,
//        array $variation
//    ): int
//    {
//        if($product = $this->user->cart->where('id', $productId)->first()
//            ->pivot->where('variations', json_encode($variation))->first()) {
//            return $product->quantity;
//        }
//        return 0;
//    }
    final public function clean(): bool
    {
        return (bool)$this->instance()->cartVariations()->delete();
    }

    final public function delete(Cart $cart): bool
    {
        return (bool)$this->instance()->cartVariations()->delete();
    }

    final public function update(CartVariation $cart, Request $request)
    {
        $qty = (int)$request->input('quantity') > 0 ? $request->input('quantity') : 1;
        $attributes = [
            'variation' => $request->input('variation'),
            'reference' => $request->input('reference'),
            'qty' => $qty,
            'price' => $request->input('price')
        ];

        foreach($attributes as $key => $value)
        {
            if(!isset($value))
            {
                unset($attributes[$key]);
            }
        }

        return $cart->update($attributes);
    }



}
