<?php

namespace App\Http\Requests\Cart\Media;

use App\Cart\Contracts\CartContractInterface;
use Illuminate\Foundation\Http\FormRequest;

class StoreMediaRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'files' => 'array',
            'files.*' => 'file'
        ];
    }

    public function prepareForValidation()
    {
        $cart = app(CartContractInterface::class); // access the cart instance
        $cartVariation = $cart->contents()->firstWhere('id', $this->item); // find cart variation

        if (!$cartVariation) { // abort if not found
            abort(404);
        }

        $this->merge([
            'cartVariation' => $cartVariation // merge the cart variation to the request
        ]);
    }
}
