<?php

namespace App\Http\Requests\Cart;

use App\Models\Tenants\Variation;
use App\Validators\PrepareCartAddValidator;
use App\Validators\PreparePrintProductCartAddValidator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;

/**
 * Class CartStoreRequest
 * @package App\Http\Requests\Cart
 * @OA\Schema(
 *     schema="CartStoreRequest",
 *     title="Cart Store Request"
 *
 * )
 */
class CartStoreRequest extends FormRequest
{
    protected array $validations = [];

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    /**
     * @OA\Property(property="Product",type="array" ,
     *       @OA\Items(
     *          @OA\Property(property="quantity", type="string", example=200),
     *          @OA\Property(property="product", type="string",description="can be prodect id came form shop or Object printing ",example=2),
     *          @OA\Property(property="variations", type="string", example="[{id:2},{id:1}]"),
     *        )
     *     ),
     */
    public function rules(): array
    {
        return [
            'mode' => 'required|string|in:print,custom',
            'variations.*.id' => 'exists:variations,id',
            'variations.*.quantity' => 'numeric|min:1',
            'quantity' => 'numeric|min:1',
            'sku' => 'nullable',
            'variations' => 'array|nullable',
            'product' => ['required', function ($attribute, $value, $fail) {
                return $value instanceof Variation ?: $fail;
            }],
            'approve' => 'boolean',
            'initBluePrint' => 'nullable',
            'cart_variation' => 'nullable|exists:cart_variation,id',
        ];
    }

    /**
     * @throws ValidationException
     */
    protected function prepareForValidation(): void
    {

        if($this->mode === 'print') {
            $this->merge((new PreparePrintProductCartAddValidator($this))->prepare());
        } else {
            $this->merge((new PrepareCartAddValidator($this))->prepare());
        }
    }
}
