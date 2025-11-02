<?php

namespace App\Http\Requests\Order;

use App\Facades\Settings;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;

final class ServiceOrderStoreRequest extends FormRequest
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
            'name' => 'required|string|max:255|min:3',
            'price' => 'required|integer|min:1',
            'qty' => 'required|integer|min:1',
            'description' => 'nullable|string|max:255|min:3',
            'vat' => 'nullable|numeric',
        ];
    }

    /**
     * @throws ValidationException
     */
    public function prepareForValidation(): void
    {
        $this->merge([
            'qty' => $this->qty ?? 1,
            'vat' => $this->vat ?? Settings::vat()->value,
        ]);

        if (!auth()->user()->can('orders-services-create') && !empty($this->services)) {
            throw ValidationException::withMessages([
                'orders_service' => __('Not permitted action.')
            ]);
        }
    }
}
