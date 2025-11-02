<?php

declare(strict_types=1);

namespace App\Http\Requests\Order;

use App\Enums\OrderOrigin;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

final class OrderStoreRequest extends FormRequest
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
            'reference' => 'string|max:100',
            'type' => 'required|boolean',
            'st' => 'integer|exists:statuses,code',
            'user_id' => 'integer|exists:users,id',
            'delivery_multiple' => 'boolean',
            'delivery_pickup' => 'boolean',
            'shipping_cost' => 'nullable|integer|min:1',
            'cost' => 'nullable|integer|min:1',
            'note' => 'string|min:3|max:255',
            'ctx_id' => 'integer|exists:contexts,id',
            'created_from' => ['string', new Enum(OrderOrigin::class)],
            'message' => 'string|nullable',
            'internal' => 'boolean',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'type' => true,
            'created_from' => OrderOrigin::FromOrder->value,
            'internal' => true
        ]);
    }
}
