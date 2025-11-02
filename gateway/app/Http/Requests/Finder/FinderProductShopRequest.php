<?php

namespace App\Http\Requests\Finder;

use App\Enums\CategoryType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class FinderProductShopRequest extends FormRequest
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
            'suppliers' => 'required|array',
            'type' => [new Enum(CategoryType::class)],
            'product' => 'nullable|array',
            'quantity' => 'required|numeric|min:1',
            'divided' => 'boolean',
        ];
    }
}
