<?php

namespace App\Http\Requests\Products;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\App;

class ProductCopyRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'name' => 'required',
            'category_id' => 'required|exists:categories,id',
            'iso' => 'required',
            'sort' => 'integer|nullable',
            'price' => 'required|integer|min:0',
            'sale_price' => 'nullable|integer|min:0',
            'free' => 'boolean',
            'description' => 'string|nullable',
            'ean' => 'string|nullable',
            'art_num' => 'string|nullable',
            'properties' => 'array|nullable',
            'stock_product' => 'boolean',
            'published' => 'boolean',
        ];
    }

    /**
     * 
     * @return void
     */
    protected function prepareForValidation()
    {
        $this->merge([
            'iso' => App::getLocale(),
            'free' => $this->free ?? false,
            'stock_product' => $this->stock_product ?? false,
            'published' => $this->published?? true,
        ]);
    }
}
