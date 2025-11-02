<?php

declare(strict_types=1);

namespace App\Http\Requests\Order;

use App\Enums\Status;
use App\Models\Tenants\Order;
use App\Validators\PrepareOrderValidator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Enum;
use Illuminate\Validation\ValidationException;

final class OrderUpdateRequest extends FormRequest
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
            'ctx_id' => [
                'sometimes',
                'integer',
                'nullable',
                'exists:contexts,id',
            ],

            'user_id' => [
                'integer',
                'nullable',
                'exists:users,id',
            ],

            'reference' => 'nullable|string|max:100',
            'st' => ['integer', new Enum(Status::class)],
            'delivery_multiple' => 'boolean|nullable',
            'delivery_pickup' => 'boolean|nullable',
            'note' => 'sometimes|nullable|string|max:255',

            'address' => [
                'sometimes',
                'nullable',
                'integer',
                'exists:addresses,id',
//                'required_if:delivery_multiple,false'
            ],

            'price' => 'nullable|integer|min:0',
            'expire_at' => 'nullable|min:0',

            'invoice_address' => [
                'sometimes',
                'nullable',
                'integer',
                'exists:addresses,id'
            ],

            'st_message' => 'required_if:st,319',
            'message' => 'string|nullable',
            'archived' => 'boolean|nullable',
            'editing' => 'nullable|boolean',
//            'different_invoice_address' => 'required|boolean'
        ];
    }

    /**
     * @return void
     * @throws ValidationException
     */
    protected function prepareForValidation(): void
    {
        $this->order = Order::where([['id', $this->order->id], ['type', true]])->first();

        if (!$this->order) {
            throw ValidationException::withMessages([
                'order' => __('The order you have requested is not available!')
            ]);
        }

        if ($this->order->locked_by && $this->order->locked_by !== Auth::user()->getAuthIdentifier()) {
            throw ValidationException::withMessages([
                'quotation' => __('The order you have requested is locked by ' . $this->order->lockedBy?->email)
            ]);
        }

        if (!auth()->user()->can('orders-type-update') && $this->has('type')) {
            throw ValidationException::withMessages([
                'type' => __('Not permitted action.')
            ]);
        }

        if (!auth()->user()->can('orders-reference-update') && $this->has('reference')) {
            throw ValidationException::withMessages([
                'reference' => __('Not permitted action.')
            ]);
        }

        if (!auth()->user()->can('orders-note-update') && $this->has('note')) {
            throw ValidationException::withMessages([
                'note' => __('Not permitted action.')
            ]);
        }

        if (!auth()->user()->can('orders-delivery-multiple-update') && $this->has('delivery_multiple')) {
            throw ValidationException::withMessages([
                'delivery_multiple' => __('Not permitted action.')
            ]);
        }

        if (!auth()->user()->can('orders-delivery-pickup-update') && $this->has('delivery_pickup')) {
            throw ValidationException::withMessages([
                'delivery_pickup' => __('Not permitted action.')
            ]);
        }

        $this->replace((new PrepareOrderValidator($this, $this->order))->prepare());
    }
}


