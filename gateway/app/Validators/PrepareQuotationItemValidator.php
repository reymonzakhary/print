<?php


namespace App\Validators;


use App\DTO\Tenant\Orders\ItemDTO;
use App\Foundation\ContractManager\Facades\ContractManager;
use App\Foundation\Status\Status;
use App\Http\Requests\Items\QuotationItemUpdateRequest;
use App\Models\Hostname;
use App\Models\Website;
use App\Services\Suppliers\SupplierCategoryService;
use App\Services\Tenant\Calculations\CalculationService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class PrepareQuotationItemValidator
{

    private ?array $product = NULL;
    private ?int $vat = null;
    private ?string $reference = NULL;
    private ?int $discount = 0;

    protected ?int $st = 300;

    protected ?string $st_message = NULL;
    private ?string $supplier_id = NULL;
    private ?string $supplier_name = NULL;
    private ?string $note = NULL;

    protected ?int $qty = NULL;
    private ?bool $delivery_pickup = false;
    private ?int $shipping_cost = NULL;
    private ?int $address = NULL;
    private ?array $addresses = NULL;
    private Model $quotation;
    private ?Model $item;
    private QuotationItemUpdateRequest $request;
    private array $response;
    private ?bool $delivery_separated = false;
    private array $delivery = [];
    private array $items = [];
    private array $price = [];
    private ?string $calculation_type = '';
    private ?string $sku_id = '';
    private ?string $category_slug = '';
    private array $category = [];
    private ?int $dlv = 0;
    private ?array $product_items = [];


    /**
     * PrepareOrderValidator constructor.
     * @param QuotationItemUpdateRequest $request
     */
    public function __construct(
        QuotationItemUpdateRequest $request
    )
    {
        $this->request = $request;
        $this->request->order = $this->request->quotation;
        $this->quotation = $this->request->quotation;
        $this->item = $this->request->order->items->firstWhere('id', $request->item);
        abort_unless($this->item, 404, __("Item not found."));
        $this->product = $this->item->product->toArray();
        $this->product_items = optional($this->product)['items'];
        $this->calculation_type = optional($this->product)['calculation_type'];
        $this->vat = $this->item->vat;
        $this->reference = $this->item->reference;
        $this->st = $this->item->st;
        $this->category_slug = optional(optional($this->product)['category'])['slug'];
        $this->category = optional($this->product)['category'];
        $this->st_message = $this->item->st_message;
        $this->supplier_id = $this->item->supplier_id;
        $this->supplier_name = $this->item->supplier_name;
        $this->note = $this->item->note;
        $this->qty = optional($this->item->pivot)->qty;
        $this->price = $this->item?->product?->price;
        $this->dlv = optional(optional($this->price)['dlv'])['days'];
        $this->delivery_pickup = (bool) optional($this->item->pivot)->delivery_pickup;
        $this->shipping_cost = optional($this->item->pivot)->shipping_cost ?? 0;
        $this->delivery_separated = $this->item->delivery_separated;
        $this->sku_id = optional($this->item)->sku_id;
    }

    /**
     * Retrieves the product details based on the calculation type.
     *
     * @return array|null
     * @throws ValidationException
     */
    final public function product(): ?array
    {
        if ($this->request->get('product')) {
            /**
             * check the sub objects
             */
            $this->product = match($this->calculation_type) {
                'full_calculation' => $this->full_calculation(),
                'semi_calculation' =>  $this->semi_calculation(),
                default => $this->open_calculation(),
            };
        }

        return $this->product;

    }

    /**
     * Perform a full calculation based on product price and additional parameters
     *
     * @return array|null Returns an array containing the updated product price details, or null if calculation fails
     * @throws ValidationException
     */
    final public function full_calculation(): ?array
    {
        $price = $this->request->input('product.price');
        $qty = optional($price)['qty'];
        $days = optional(optional($price)['dlv'])['days'];
        $vat = optional($price)['vat'];
        $gross_price = optional($price)['gross_price'];
        /** check if gross price  */
        if ($gross_price) {
            $this->product['price'] = ItemDTO::fromGrossPrice(
                $this->category_slug,
                $this->price,
                $gross_price,
                $qty,
                $days,
                $vat
            );

            return $this->product;
        }

        if ($this->item->connection !== tenant()->uuid){
            $current_tenant = tenant()->uuid;
            $supplierHostnameId = Website::query()
                ->where('uuid', $this->item->connection)
                ->with(['hostname' => function($query) {
                    $query->select('id', 'website_id');
                }])
                ->first(['id'])->hostname->id;
            $contract = ContractManager::getContractsBetween(
                Hostname::class,
                \hostname()->id,
                Hostname::class,
                $supplierHostnameId)
                ->first();

            $discount = optional(optional(optional($contract)->custom_fields)->contract)['discount'];
            switchTenant( $this->item->connection);
            $proxy = app(CalculationService::class)
                ->obtainCalculatedShopPrices(
                    $this->category_slug,
                    [
                        'product' => $this->product['product'],
                        'dlv' => $days ?? $this->dlv,
                        'divided' => optional($this->product)['divided'],
                        'quantity' => $qty ?? $this->qty,
                        'bleed' => optional($this->category)['bleed'] ?? 0,
                        'vat' => $vat ?? $this->vat,
                        'vat_override' =>  isset($vat),
                        'contract' => $discount
                    ]
                );
            switchTenant($current_tenant);
        }else{
            $proxy = app(CalculationService::class)
                ->obtainCalculatedShopPrices(
                    $this->category_slug,
                    [
                        'product' => $this->product['product'],
                        'dlv' => $days ?? $this->dlv,
                        'divided' => optional($this->product)['divided'],
                        'quantity' => $qty ?? $this->qty,
                        'bleed' => optional($this->category)['bleed'] ?? 0,
                        'vat' => $vat ?? $this->vat,
                        'vat_override' =>  isset($vat)
                    ]
                );
        }

        return isset($proxy['status']) && $proxy['status'] != 200 ?
            throw ValidationException::withMessages([
                'errors' => [
                    __($proxy['message'])
                ]
            ]) :
            ItemDTO::fromUpdateFullCalculation($proxy);
    }

    /**
     * @return array|null
     * @throws ValidationException
     */
    final public function semi_calculation(): ?array
    {

        $price = $this->request->input('product.price');


        $qty = optional($price)['qty']?? $this->qty;
        $days = optional(optional($price)['dlv'])['days']?? $this->dlv;
        $vat = optional($price)['vat'];
        $gross_price = optional($price)['gross_price'];

        /** check if gross price  */
        if ($gross_price) {
            $this->product['price'] = ItemDTO::fromGrossPrice(
                $this->category_slug,
                $this->price,
                $gross_price,
                $qty,
                $days,
                $vat
            );

            return $this->product;
        }

        if ($this->item->connection !== tenant()->uuid){
            $current_tenant = tenant()->uuid;
            $supplierHostnameId = Website::query()
                ->where('uuid', $this->item->connection)
                ->with(['hostname' => function($query) {
                    $query->select('id', 'website_id');
                }])
                ->first(['id'])->hostname->id;
            $contract = ContractManager::getContractsBetween(
                Hostname::class,
                \hostname()->id,
                Hostname::class,
                $supplierHostnameId)
                ->first();

            $discount = optional(optional(optional($contract)->custom_fields)->contract)['discount'];
            switchTenant( $this->item->connection);
            $proxy = app(CalculationService::class)
                ->obtainSemiCalculatedShopPrices(
                    $this->category_slug, [
                        'product' => $this->product['product'],
                        'dlv' => $days ?? $this->dlv,
                        'divided' => optional($this->product)['divided'],
                        'quantity' => $qty ?? $this->qty,
                        'bleed' => optional($this->category)['bleed'] ?? 0,
                        'vat' => $vat ?? $this->vat,
                        'vat_override' => isset($vat),
                        'contract' => $discount
                    ]
                );
            switchTenant($current_tenant);

        } else {
            $proxy = app(CalculationService::class)
                ->obtainSemiCalculatedShopPrices(
                    $this->category_slug, [
                        'product' => $this->product['product'],
                        'dlv' => $days ?? $this->dlv,
                        'divided' => optional($this->product)['divided'],
                        'quantity' => $qty ?? $this->qty,
                        'bleed' => optional($this->category)['bleed'] ?? 0,
                        'vat' => $vat ?? $this->vat,
                        'vat_override' => isset($vat)
                    ]
                );
        }

        return isset($proxy['status']) && $proxy['status'] != 200 ?
            throw ValidationException::withMessages([
                'errors' => [
                    $proxy['message']
                ]
            ]) :
            ItemDTO::fromUpdateSemiCalculation($proxy);
    }

    /**
     * @return array|null
     */
    final public function open_calculation(): ?array
    {
        return  [
            "custom" => false,
            "type" => 'print',
            "signature" => null,
            "product_id" => null,
            "product_name" => null,
            "product_slug" => null,
            "hasVariation" => false,
            "variation" => [],
            "calculation_type" => $this->calculation_type,
            "items" => ItemDTO::fromProductToItems($this->request->input('product.product'))?:$this->product['items'],
            "product" => $this->request->input('product.product')?:$this->product['product'],
            "connection" => $this->request->input('product.connection')?:$this->product['connection'],
            "tenant_id" => $this->request->input('product.tenant_id')?:$this->product['tenant_id'],
            "tenant_name" => $this->request->input('product.tenant_name')?:$this->product['tenant_name'],
            "external" => $this->request->input('product.external')?:$this->product['external'],
            "external_id" => $this->request->input('product.external_id')?:$this->product['external_id'],
            "external_name" => $this->request->input('product.external_name')?:$this->product['external_name'],
            "category" => $this->category,
            "margins" => $this->request->input('product.margins')?:$this->product['margins'],
            "divided" => $this->request->input('product.divided')?:$this->product['divided'],
            "quantity" => $this->request->input('product.quantity')?:$this->product['quantity'],
            "calculation" => $this->request->input('product.calculation')?:$this->product['calculation'],
            "price" => ItemDTO::fromOpenProductPrice(
                $this->product['category'],
                $this->product['price'],
                $this->request->input('product.price')?:$this->product['price']
            ),
        ];
    }

    /**
     * This is a sub method to handle the sub object of the product
     * @return array|null
     */
    final public function items(): ?array
    {
        if ($this->request->get('items') && !in_array(
                $this->calculation_type, ['full_calculation', 'semi_calculation']
            )) {
            return $this->request->get('items');
        }
        return $this->items;
    }

    /**
     * @return int
     * @throws ValidationException
     */
    final public function vat(): int
    {
        if ($this->request->get('vat') > 100 || $this->request->get('vat') < 0) {
            throw ValidationException::withMessages([
                'vat' => [
                    __("Vat value should be between 0 and 100."),
                ]
            ]);
        }
        return $this->vat = $this->request->get('vat');
    }

    /**
     * @return string|null
     */
    final public function reference(): ?string
    {
        if ($this->request->get('reference')) {
            $this->reference = $this->request->get('reference');
        }
        return $this->reference;
    }

    /**
     * @return int
     */
    final public function discount(): int
    {
        return $this->discount = $this->request->get('discount');
    }

    /**
     * @return int|null
     * @throws ValidationException
     */
    final public function st(): ?int
    {
        return $this->st = $this->request->get('st');
    }

    /**
     * @return string|null
     * @throws ValidationException
     */
    final public function st_message(): ?string
    {
        if ($this->st === Status::REJECTED && !$this->request->get('st_message')) {
            throw ValidationException::withMessages([
                'st_message' =>
                    __("Status message is required.")
            ]);
        }

        return $this->st_message = $this->request->get('st_message');
    }

    /**
     * @return string|null
     * @throws ValidationException
     */
    final public function supplier_id(): ?string
    {
        if (!Str::isUUID($this->request->get('supplier_id'))) {
            throw ValidationException::withMessages([
                'supplier_id' =>
                    __("The supplier id must be a valid UUID.")
            ]);
        }
        /***
         * Todo:: Reymon can you check this condition please
         */
        if (
            $this->request->get('supplier_id') &&
            $this->request->get('supplier_id') === tenant()->uuid
        ) {
            $this->supplier_name = request()->hostname->fqdn;
            return $this->supplier_id = $this->request->get('supplier_id');
        }

        $website = Website::where('uuid', $this->request->get('supplier_id'))->with('hostnames')->first();
        if ($supplier = optional(optional($website)->hostnames->where('configure.assortments.shared', true)->first())) {
            $this->supplier_name = $supplier->fqdn;

            return $this->supplier_id = $this->request->get('supplier_id');
        }

        throw ValidationException::withMessages([
            'supplier_id' =>
                __('The selected supplier not exists.')
        ]);
    }

    /**
     * @return string|null
     */
    final public function supplier_name(): ?string
    {
        if ($this->request->get('supplier_name')) {
            return $this->supplier_name = $this->request->get('supplier_name');
        }
        return $this->supplier_name;
    }

    /**
     * @return string|null
     */
    final public function note(): ?string
    {
        if ($this->request->get('note')) {
            return $this->note = $this->request->get('note');
        }
        return $this->note;
    }

    /**
     * @return bool
     */
    final public function delivery_pickup(): bool
    {
        if ($this->request->has('delivery_pickup')) {
            $this->delivery_pickup = $this->request->get('delivery_pickup');
            $this->item->addresses()->detach();
        }
        return $this->delivery_pickup;
    }

    /**
     * @return int
     */
    final public function shipping_cost(): int
    {
        if (!is_null($this->request->get('shipping_cost'))) {
            $this->shipping_cost = $this->request->get('shipping_cost');
        } else {
            $this->shipping_cost = optional($this->item->pivot)->shipping_cost;
        }

        return $this->shipping_cost;
    }


    /**
     * @return bool
     */
    final public function delivery_separated(): bool
    {
        return $this->delivery_separated = $this->request->get('delivery_separated');
    }

    final public function delivery_separated_addresses(): ?array
    {
        if ($this->delivery_separated = $this->request->get('delivery_separated')) {
            return ['addresses'];
        }
        return ['address'];
    }

    /**
     * @return int|null
     * @throws ValidationException
     */
    final public function address(): ?int
    {

        if ($this->delivery_separated) {
            $this->address = null;
            return $this->address;
        }
        if ($user = $this->request->order->orderedBy) {
            if (!$user
                ->addresses()
                ->where('addresses.id', $this->request->address)
                ->exists()) {
                throw ValidationException::withMessages([
                    'address' =>
                        __('The selected Address is not related to the existing user.')
                ]);
            }
        } else {
            throw ValidationException::withMessages([
                'user_id' =>
                    __('There is no customer selected.')
            ]);
        }

        $this->address = $this->request->get('address');

        return $this->address;
    }

    /**
     * @return array|null
     * @throws ValidationException
     */
    final public function addresses(): ?array
    {
        if (!$this->delivery_separated) {
            $this->addresses = null;
            return $this->addresses;
        }

        if ($user = $this->request->order->orderedBy) {

            $qty = 0;
            foreach ($this->request->get('addresses') as $address) {
                if (!$address['delivery_pickup'] && !$user
                        ->addresses()
                        ->where('addresses.id', $address['address'])
                        ->exists()) {
                    throw ValidationException::withMessages([
                        'address' =>
                            __("The selected Address id {$address['address']} is not related to the existing user.")
                    ]);
                }
                //$this->item->children()->get());
                $qty += $address['qty'];
            }
            if ($qty > $this->item->order->first()->pivot->qty) {
                throw ValidationException::withMessages([
                    'qty' =>
                        __('The selected total quantity is higher then the available quantity')
                ]);
            }
        } else {
            throw ValidationException::withMessages([
                'user_id' =>
                    __('The user id is required.')
            ]);
        }

        $this->addresses = $this->request->get('addresses');

        return $this->addresses;
    }



    /**
     * order reference to push to the client
     *
     * @return array
     */
    final public function excludes(): array
    {
        $methods = get_class_methods($this);

        $ex = [
            '__construct',
            '__call',
            'prepare',
            'excludes',
            'delivery_separated_addresses',
            'full_calculation',
            'semi_calculation',
            'open_calculation',
        ];
        if ($this->request->order->delivery_multiple) {
            if (!$this->request->get('delivery_pickup')) {
                $ex = array_merge($ex, $this->delivery_separated_addresses());
            } else {
                $ex = array_merge($ex, ['addresses']);
            }
        } else {
            $ex = array_merge($ex, ['addresses', 'address', 'delivery_pickup']);
        }

        return array_values(array_diff($methods, $ex));
    }


    /**
     * @return array|null
     */
    final public function prepare(): ?array
    {

        $methods = $this->excludes();
        foreach ($methods as $method) {
            if (array_key_exists($method, $this->request->all())) {
                $this->response[$method] = $this->{$method}();
            } else if (!in_array($method, ['price'])) {
                $this->response[$method] = $this->{$method};
            }
        }
        !$this->request->get('vat') || !$this->request->get('shipping_cost') ?:
            $this->response['product']['price'] = $this->price;
        return $this->response;
    }

    public function __call($method, $args)
    {
        return;
    }
}
