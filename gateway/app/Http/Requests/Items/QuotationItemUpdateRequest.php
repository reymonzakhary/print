<?php

namespace App\Http\Requests\Items;

use App\Services\Suppliers\SupplierCategoryService;
use App\Validators\PrepareQuotationItemValidator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;

final class QuotationItemUpdateRequest extends FormRequest
{
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
    public function rules(): array
    {
        return [
            'delivery_pickup' => [
                'nullable',
                'boolean'
            ],
            'delivery_separated' => [
                'nullable',
                'boolean'
            ],

            'addresses' => [
                'nullable', 'array'
            ],

            'addresses.address' => [
                'integer',
            ],

            'addresses.qty' => [
                'integer',
            ],
            'product.price.qty' => [
                'required',
                'integer',
                'min:1',
                'max:1000000000000000000'
            ],

            'addresses.delivery_pickup' => [
                'integer',
            ],

            'address' => [
                'nullable',
                'integer',
                'exists:addresses,id',
            ],

            'st' => 'integer|exists:statuses,code',
            'st_message' => 'required_if:st,319',
            'shipping_cost' => 'numeric|nullable',
            'reference' => 'nullable|string|max:100',
            'note' => 'nullable|string|min:3|max:255',


            'items' => 'nullable|array',
            'items.*.key_link' => 'nullable|string',
            'items.*.key_id' => 'nullable|string',
            'items.*.key_appendage' => 'boolean|nullable', // Adjust if necessary
            'items.*.key_calc_ref' => 'nullable|string',
            'items.*.key_start_cost' => 'nullable|numeric',
            'items.*.key_display_name' => 'nullable|array',
            'items.*.key_incremental' => 'nullable|boolean',
            'items.*.key' => 'nullable|string',
            'items.*.value_link' => 'nullable|string',
            'items.*.value' => 'nullable|string',
            'items.*.value_id' => 'nullable|string',
            'items.*.value_dimension' => 'nullable|string',
            'items.*.value_display_name' => 'nullable|array',
            'items.*.value_dynamic' => 'nullable|boolean',
            'items.*.value_unit' => 'nullable|string', // Adjust to nullable if necessary
            'items.*.value_width' => 'nullable|integer',
            'items.*.value_maximum_width' => 'nullable|integer',
            'items.*.value_minimum_width' => 'nullable|integer',
            'items.*.value_height' => 'nullable|integer',
            'items.*.value_maximum_height' => 'nullable|integer',
            'items.*.value_minimum_height' => 'nullable|integer',
            'items.*.value_length' => 'nullable|integer',
            'items.*.value_minimum_length' => 'nullable|integer',
            'items.*.value_maximum_length' => 'nullable|integer',
            'items.*.value_start_cost' => 'nullable|integer',

            'product' => 'nullable|array',
            'product.*.key' => 'nullable|string',
            'product.*.value' => 'nullable|string',
            'product.*.divider' => 'nullable|string',
            'product.*.dynamic' => 'nullable|boolean',
            'product.*._' => 'nullable|array',
            'product.quantity' => 'nullable|integer|between:0,1000000000000',

            'price.qty' => 'nullable|integer|min:1',
            'price.dlv.days' => 'nullable|integer|min:0',
            'price.vat' => ['nullable','numeric','between:0,100','regex:/^\d{1,2}(\.\d{1,2})?$/'],
            'price.gross_price' => ['nullable', 'regex:/^\d+(\.\d{1,3})?$/'],
        ];
    }

    /**
     * Prepare the request for validation.
     *
     * @throws ValidationException
     */
    public function prepareForValidation(): void
    {

        if (!auth()->user()->can('quotations-items-note-update') && $this->has('note')) {
            throw ValidationException::withMessages([
                'orders_items_note' => __('Not permitted action.')
            ]);
        }

        if (!auth()->user()->can('quotations-items-reference-update') && $this->has('reference')) {
            throw ValidationException::withMessages([
                'quotations-items-reference' => __('Not permitted action.')
            ]);
        }

        if (!auth()->user()->can('quotations-items-product-update') && !empty($this->product)) {
            throw ValidationException::withMessages([
                'quotations-items-product' => __('Not permitted action.')
            ]);
        }

        if (!auth()->user()->can('quotations-items-product-delivery-days-update') && !empty(optional(optional(optional($this->product)['prices']))['dlv'])) {
            throw ValidationException::withMessages([
                'quotations-items-product-prices' => __('Not permitted action.')
            ]);
        }

        if (!auth()->user()->can('quotations-items-product-prices-update') && !empty(optional(optional(optional($this->product)['prices']))['p'])) {
            throw ValidationException::withMessages([
                'quotations-items-product-prices' => __('Not permitted action.')
            ]);
        }

        if (!auth()->user()->can('quotations-items-delivery-separated-update') && $this->has('delivery_separated')) {
            throw ValidationException::withMessages([
                'delivery_separated' => __('Not permitted action.')
            ]);
        }

        if (!auth()->user()->can('quotations-items-st-update') && $this->has('st')) {
            throw ValidationException::withMessages([
                'st' => __('Not permitted action.')
            ]);
        }

        if (!auth()->user()->can('quotations-items-st-message-update') && $this->has('st_message')) {
            throw ValidationException::withMessages([
                'st_message' => __('Not permitted action.')
            ]);
        }

        $this->replace((new PrepareQuotationItemValidator($this))->prepare());
    }

    public function messages(): array
    {
        return [
            'product.price.qty.max' => __('The quantity you have entered is too large.'),
        ];
    }

}
