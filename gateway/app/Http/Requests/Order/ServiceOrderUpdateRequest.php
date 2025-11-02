<?php

declare(strict_types=1);

namespace App\Http\Requests\Order;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;

final class ServiceOrderUpdateRequest extends FormRequest
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
            'vat' => 'nullable|between:0,99.99',
            'qty' => 'nullable|integer',
            'price' => 'nullable|integer',
        ];
    }

    /**
     * @throws ValidationException
     */
    public function prepareForValidation(): void
    {
        if (!auth()->user()->can('orders-services-update')) {
            throw ValidationException::withMessages([
                'orders_service' => __('Not permitted action.')
            ]);
        }
    }
}
