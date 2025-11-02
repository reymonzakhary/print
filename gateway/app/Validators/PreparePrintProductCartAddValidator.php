<?php

namespace App\Validators;

use App\DTO\Tenant\Orders\ItemDTO;
use App\Facades\Crypto;
use App\Http\Requests\Cart\CartStoreRequest;
use App\Services\Suppliers\SupplierCategoryService;
use App\Services\Tenant\Calculations\CalculationService;
use Illuminate\Validation\ValidationException;

class PreparePrintProductCartAddValidator
{

    protected ?CartStoreRequest $request;

    protected mixed $product;

    protected mixed $sku;

    protected mixed $variations;

    protected mixed $variation;

    protected int $quantity;

    protected mixed $template;

    protected mixed $productTemplate;

    protected mixed $variationTemplate;

    protected mixed $validations;

    protected mixed $blueprint;

    protected mixed $pipeline;

    protected mixed $price;

    protected mixed $mode;

    protected bool $check_hash;

    protected mixed $files;

    protected mixed $response;

    protected mixed $category;
    protected mixed $category_slug;

    protected mixed $category_id;

    protected mixed $category_name;
    protected ?int $dlv = null;
    protected bool $divided = false;

    /**
     * @param CartStoreRequest $request
     */
    public function __construct(
        CartStoreRequest $request
    )
    {
        $this->request = $request->merge([
            'product' => $request->product,
            'price' => $request->price,
            'quantity' => (int)$request->quantity,
            'mode' => $request->mode,
            'dlv' => (int) optional(optional($request->price)['dlv'])['days'],
            'divided' => filter_var($request->divided, FILTER_VALIDATE_BOOLEAN)
        ]);

        $this->mode = $this->request->mode;
        $this->product = $this->request->product;
        $this->price = $this->request->price;
        $this->quantity = (int) $this->request->quantity;
        $this->category_slug = $this->request->category_slug;
        $this->category_id = $this->request->category_id;
        $this->category_name = $this->request->category_name;
        $this->dlv = $this->request->dlv;
        $this->divided = $this->request->divided;
    }

    /**
     * Get the product.
     *
     * @return mixed The product data.
     * @throws ValidationException If the product is missing or empty.
     */
    public function product(): mixed
    {
        if (empty($this->product)) {
            throw ValidationException::withMessages([
                'product' => __('The product field is required')
            ]);
        }

        return $this->product;
    }

    /**
     *
     * @
     * @throws ValidationException
     */
    public function price(): mixed
    {
        if (!$this->price) {
            throw ValidationException::withMessages([
                'price' => __('price is required')
            ]);
        }
        return $this->price;
    }

    /**
     * @throws ValidationException
     */
    public function check_hash(): void
    {
        $id = optional($this->price)['p'].'_'.optional(optional($this->price)['dlv'])['days'].'_'.optional($this->price)['qty'].'_'.tenant()->uuid.'_'.optional($this->price)['ppp'];
        if (!Crypto::salt(env('CALCULATION_SHA_PHRASE'))
            ->algorithm(env('CALCULATION_HASH_ALGORITHM'))
            ->check($id, optional($this->price)['id'])
        ){
            throw ValidationException::withMessages([
                'price' => __('Not valid price.')
            ]);
        }
    }


    /**
     * @return int
     * @throws ValidationException
     */
    public function quantity(): int
    {
        if (!$this->quantity) {
            throw ValidationException::withMessages([
                'quantity' => __('quantity is required')
            ]);
        }
        return $this->quantity;
    }

    /**
     * @return int
     * @throws ValidationException
     */
    public function dlv(): int
    {
        if (is_null($this->dlv)) {
            throw ValidationException::withMessages([
                'dlv' => __('dlv is required')
            ]);
        }
        return $this->dlv;
    }

    /**
     *
     * @return bool
     */
    public function divided(): bool
    {
        return $this->divided;
    }

    /**
     *
     * @return mixed
     */
    public function mode(): mixed
    {
        return $this->mode;
    }

    /**
     *
     * @return string
     * @throws ValidationException
     */
    public function category_slug(): string
    {
        if (!$this->category_slug) {
            throw ValidationException::withMessages([
                'category_slug' => __('Category slug is required')
            ]);
        }
        return $this->category_slug;
    }

    /**
     * @return array
     * @throws ValidationException
     */
    public function category(): array
    {
        $this->category = app(SupplierCategoryService::class)->obtainCategoryObject($this->category_slug);
        if (!$this->category) {
            throw ValidationException::withMessages([
                'category' => __('Category not found!')
            ]);
        }
        return $this->category;
    }

    /**
     * @throws ValidationException
     */
    public function priceBuild(): void
    {
        match (optional(optional($this->category)['price_build'])['full_calculation']) {
            true => $this->fullCalculation(),
            false => $this->semiCalculation(),
            default => throw ValidationException::withMessages([
                'category' => __('There is no valid calculation method selected.')
            ])
        };
    }

    /**
     * @throws ValidationException
     */
    public function fullCalculation(): array
    {
        $request = [
            'vat' => $this->price['vat'],
            'vat_override' => true,
            "product" => $this->product,
            "dlv" => $this->dlv,
            "divided" => $this->divided,
            "quantity" => (int) $this->quantity
        ];

        $proxy = app(CalculationService::class)
            ->obtainCalculatedShopPrices(
                $this->category_slug,
                $request,
                request()->query()
            );

        if(optional($proxy)['status'] && optional($proxy)['status'] !== 200) {
            throw ValidationException::withMessages([
                'product' => __(optional($proxy)['message'])
            ]);
        }

        $result = ItemDTO::fromFullCalculation($proxy);

        $result['price'] = collect($result['prices'])->first();

        if (optional(optional($result)['price'])['id'] !== optional($this->price)['id']) {
            throw ValidationException::withMessages([
                'price' => __('Not valid price.')
            ]);
        }

        unset($result['prices']);

        return $this->response = $result;
    }


    /**
     * @throws ValidationException
     */
    public function semiCalculation(): array
    {
        $request = [
            "product" => $this->product,
            "dlv" => $this->dlv,
            "divided" => $this->divided,
            "quantity" => (int) $this->quantity
        ];

        $proxy = app(CalculationService::class)
            ->obtainSemiCalculatedShopPrices(
                $this->category_slug,
                $request,
                request()->query()
            );

        if(optional($proxy)['status'] && optional($proxy)['status'] !== 200) {
            throw ValidationException::withMessages([
                'product' => __(optional($proxy)['message'])
            ]);
        }

        $result = ItemDTO::fromSemiCalculation($proxy);

        $result['price'] = collect($result['prices'])->filter(function ($price) { return optional(optional($price)['dlv'])['days'] === $this->dlv; })->first();

        if (optional(optional($result)['price'])['id'] !== optional($this->price)['id']) {
            throw ValidationException::withMessages([
                'price' => __('Not valid price.')
            ]);
        }

        unset($result['prices']);

        return $this->response = $result;
    }

    /**
     *
     * @return array
     */
    final public function delivery(): array
    {
        $methods = get_class_methods($this);
        $ex = [
            '__construct',
            'prepare',
            'delivery',
            'fullCalculation',
            'semiCalculation'

        ];

        return array_values(array_diff($methods, $ex));
    }

    /**
     * @return array|null
     */
    final public function prepare(): ?array
    {
        $methods = $this->delivery();
        foreach ($methods as $method) {
            $this->response[$method] = $this->{$method}();
        }
        return $this->response;
    }
}
