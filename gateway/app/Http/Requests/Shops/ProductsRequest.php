<?php

namespace App\Http\Requests\Shops;

use App\Enums\CategoryType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;
use Illuminate\Validation\ValidationException;

class ProductsRequest extends FormRequest
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
            'type' => [new Enum(CategoryType::class)],
            'product' => 'nullable|array',
            'quantity' => 'numeric|min:1'
        ];
    }

    public function prepareForValidation()
    {
        if (empty($this->product) && $this->type == CategoryType::PRINT->value) {
            throw ValidationException::withMessages([
                "product" => __('Product cannot be empty.')
            ]);
        }

    }
}
