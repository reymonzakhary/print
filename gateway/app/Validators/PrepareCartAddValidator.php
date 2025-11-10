<?php

namespace App\Validators;

use App\Blueprint\Contract\BlueprintFactoryInterface;
use App\Http\Requests\Cart\CartStoreRequest;
use App\Models\Tenant\Sku;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class PrepareCartAddValidator
{

    protected ?CartStoreRequest $request;

    protected string $product_id;

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

    protected string $mode;

    protected mixed $files;

    protected mixed $response;


    /**
     * @param CartStoreRequest $request
     * @throws ValidationException
     */
    public function __construct(
        CartStoreRequest $request
    )
    {
        $this->blueprint = app(BlueprintFactoryInterface::class);


        $this->request = $request->merge([
            'mode' => null,
            'product' => $request->product,
            'variations' => $request->variations,
            'quantity' => (int)$request->quantity,
            'sku' => null,
            'template' => null,
//            'files' => null,
//            'initBluePrint' => null,
//            'blueprint' => null,
            'type' => 'sku'
        ]);

        if (empty($request->product)) {
            throw ValidationException::withMessages([
                'product' => __('The product field is required')
            ]);
        }
        $this->mode = 'custom';
        $this->product_id = $request->product;
        $this->product = null;
        $this->variations = $request->variations;
        $this->quantity = $request->quantity;
        $this->sku = null;
        $this->template = null;
//        $this->files = null;
    }


    /**
     * @return string
     */
    public function mode(): string
    {
        return $this->mode = is_numeric($this->product_id) ? 'custom' : 'print';
    }

    /**
     * @return mixed
     */
    public function sku(): mixed
    {
        return $this->sku = $this->{"get" . Str::ucfirst($this->mode) . "Product"}(
            sku: $this->product_id
        );
    }

    /**
     * Retrieve the product associated with the SKU.
     *
     * @return mixed The product object.
     */
    public function product(): mixed
    {
        $this->product = $this->sku->product;
        collect($this->product->properties)
            ->reject(fn($v, $k) => $k === 'props')
            ->map(function ($property, $k) {
                if ($k === 'template') {
                    $this->template = $this->getTemplate($property);
                } elseif ($k === 'validations') {
                    $this->validate(
                        property: $property
                    );
                    $properties = array_keys(array_merge(...$property));
                    $this->files = collect($this->request->all())
                        ->reject(fn($v, $k) => !in_array($k, $properties, true))
                        ->toArray();

                }
            });

        return $this->product;
    }

    /**
     * Retrieves and processes variations for the product.
     *
     * @return mixed
     */
    public function variations(): mixed
    {

        $keys = collect($this->variations)
            ->keyBy('id')
            ->keys()
            ->toArray();


        $this->variations = $this->product->variations->whereIn('id', $keys)->unique();
        $this->variations = collect($this->variations)->map(function ($v, $index) {

            $variation = collect($v->properties)->map(function ($property, $k) use ($index, $v) {
                $variationTemplate = null;
                if ($k === 'template') {
                    $variationTemplate = $this->getTemplate($property);
                } elseif ($k === 'validations') {
                    $this->validate(
                        property: $property,
                        type: 'variations',
                        index: $index
                    );
                }
                return [
                    'id' => $v->id,
                    'variation' => $v,
                    'template' => $variationTemplate,
                    'files' => collect($this->request->variations[$index])
                        ->reject(fn($v, $k) => $k === 'id')
                        ->toArray()
                ];
            })->values()->unique()->toArray();

            return array_merge([
                'id' => $v->id,
                'variation' => [
                    "id" => $v->id,
                    "product_id" => $v->product_id,
                    "box_id" => $v->box_id,
                    "option_id" => $v->option_id,
                    "sku" => $v->sku,
                    "sku_id" => $v->sku_id,
                    "input_type" => $v->input_type,
                    "margin_value" => $v->margin_value,
                    "margin_type" => $v->margin_type,
                    "discount_value" => $v->discount_value,
                    "discount_type" => $v->discount_type,
                    "price" => $v->price->amount(),
                    "single" => $v->single,
                    "upto" => $v->upto,
                    "mime_type" => $v->mime_type,
                    "parent_id" => $v->parent_id,
                    "sort" => $v->sort,
                    "incremental" => $v->incremental,
                    "incremental_by" => $v->incremental_by,
                    "published" => $v->published,
                    "override" => $v->override,
                    "default_selected" => $v->default_selected,
                    "switch_price" => $v->switch_price,
                    "properties" => $v->properties,
                    "expire_date" => $v->expire_date,
                    "appendage" => $v->appendage,
                    "child" => $v->child,
                    "expire_after" => $v->expire_after,
                ],
                'template' => null,
                'files' => null,
                'blueprint' => [
                    'queue_id' => null
                ]
            ], $variation);
        })->toArray();

        $this->request->merge([
            'variations' => $this->variations
        ]);
        return $this->variations;
    }

    /**
     * @return mixed
     */
    public function template(): mixed
    {
        return $this->template;
    }

//    /**
//     * @return mixed
//     */
//    public function files(): mixed
//    {
//        return $this->files;
//    }

//    /**
//     * @return mixed
//     */
//    public function initBluePrint(): mixed
//    {
//        return $this->pipeline = $this->blueprint->init($this->product)
//            ->processors()
//            ->run();
//    }
//
//    /**
//     * @return mixed
//     */
//    public function blueprint(): mixed
//    {
//        return $this->pipeline = $this->product->blueprint()->first()?->configuration;
//    }

    /**
     * @return array|null
     */
    final public function prepare(): ?array
    {
        $methods = $this->delivery();
        foreach ($methods as $method) {
            if (array_key_exists($method, $this->request->toArray())) {
                $this->response[$method] = $this->{$method}();
            } else {
                $this->response[$method] = $this->{$method};
            }
        }
        return $this->response;
    }

    /**
     * @return array
     */
    private function delivery(): array
    {
        return array_values(array_diff(get_class_methods($this), [
            '__construct',
            'prepare',
            'delivery',
            'getCustomProduct',
            'getPrintProduct',
            'validate',
            'getTemplate',
//            'initBluePrint',
//            'pipeline'
        ]));
    }

    /**
     * @param $sku
     * @return mixed
     * @throws ValidationException
     */
    private function getCustomProduct(
        $sku
    ): mixed
    {
        if ($sku = Sku::where('id', $sku)->with('product')->first()) {
            return $sku;
        }
        throw ValidationException::withMessages([
            'product' => [
                __('Invalid product identifier!')
            ]
        ]);
    }

    private function getPrintProduct(
        $sku
    )
    {

//        dd($sku);
        return [];
    }

    /**
     * @param array $property
     * @return mixed
     */
    private function getTemplate(
        array $property
    ): mixed
    {
        if (count($property) > 0) {
            $model = "\App\\Models\\Tenants\\{$property['mode']}";
            if ((class_exists($model))) {
                return $model::find($property['id']);
            }
        }
        return [];
    }

    /**
     * @param array       $property
     * @param string|null $type
     * @param int         $index
     * @throws ValidationException
     */
    private function validate(array $property = [], string $type = null, int $index = 0): void
    {
        $validation = array_merge(...$property);
        if (!is_null($type)) {
            foreach (array_keys($validation) as $key) {
                $validation["{$type}.{$index}.{$key}"] = $validation[$key];
                unset($validation[$key]);
            }
        }
        $validator = Validator::make($this->request->all(), $validation);
        if ($validator->fails()) {
            throw ValidationException::withMessages([
                optional($validator->errors()->keys())[0] => [
                    $validator->messages()->first()
                ]
            ]);
        }
    }
}
