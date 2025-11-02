<?php

namespace App\Http\Requests\Order;

use App\Models\Tenants\Quotation;
use App\Validators\PrepareQuotationValidator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

final class QuotationUpdateRequest extends FormRequest
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
            'type' => 'boolean',
            'team_id' => 'integer|exists:teams,id',

            'ctx_id' => [
                'sometimes',
                'integer',
                'nullable',
                'exists:contexts,id',
            ],

            'discount_id' => [
                'sometimes',
                'integer',
                'nullable',
                'exists:discounts,id',
            ],

            'user_id' => [
                'integer',
                'nullable'
            ],

            'reference' => 'nullable|string|max:100',
            'st' => 'integer|exists:statuses,code',
            'delivery_multiple' => 'boolean|nullable',
            'delivery_pickup' => 'boolean|nullable',
            'note' => 'sometimes|nullable|string|max:255',
            'editing' => 'nullable|boolean',

            'address' => [
                'sometimes',
                'nullable',
                'integer',
                'exists:addresses,id',
//                'required_if:delivery_multiple,false'
            ],

            'invoice_address' => [
                'sometimes',
                'nullable',
                'integer',
                'exists:addresses,id',
//                'required_if:delivery_multiple,false'
            ],

            'price' => 'nullable|integer|min:0',
            'expire_at' => 'nullable',
            'message' => 'string|nullable',
            'author_id' => 'nullable|exists:users,id'
        ];
    }

    /**
     * @return void
     * @throws ValidationException
     */
    protected function prepareForValidation(): void
    {
        $this->quotation = Quotation::where([['id', $this->quotation->id], ['type', false]])->first();

        if (!$this->quotation) {
            throw ValidationException::withMessages([
                'quotation' => __('The quotation you have requested is not available!')
            ]);
        }

        if ($this->quotation->locked_by && $this->quotation->locked_by !== Auth::user()->getAuthIdentifier()) {
            throw ValidationException::withMessages([
                'quotation' => __('The quotation you have requested is locked by ' . $this->quotation->lockedBy?->email)
            ]);
        }

        if (!auth()->user()->can('quotations-user-update') && $this->has('user_id')) {
            throw ValidationException::withMessages([
                'quotations_user' => __('Not permitted action.')
            ]);
        }

        if (!auth()->user()->can('quotations-type-update') && $this->has('type')) {
            throw ValidationException::withMessages([
                'type' => __('Not permitted action.')
            ]);
        }

        if (!auth()->user()->can('quotations-reference-update') && $this->has('reference')) {
            throw ValidationException::withMessages([
                'reference' => __('Not permitted action.')
            ]);
        }

        if (!auth()->user()->can('quotations-note-update') && $this->has('note')) {
            throw ValidationException::withMessages([
                'note' => __('Not permitted action.')
            ]);
        }

        if (!auth()->user()->can('quotations-delivery-multiple-update') && $this->has('delivery_multiple')) {
            throw ValidationException::withMessages([
                'delivery_multiple' => __('Not permitted action.')
            ]);
        }

        if (!auth()->user()->can('quotations-delivery-pickup-update') && $this->has('delivery_pickup')) {
            throw ValidationException::withMessages([
                'delivery_pickup' => __('Not permitted action.')
            ]);
        }

        if (!auth()->user()->can('quotations-address-update') && $this->has('address')) {
            throw ValidationException::withMessages([
                'quotations_address' => __('Not permitted action.')
            ]);
        }

        if (!auth()->user()->can('quotations-invoice-address-update') && $this->has('invoice_address')) {
            throw ValidationException::withMessages([
                'invoice_address' => __('Not permitted action.')
            ]);
        }

        $this->replace((new PrepareQuotationValidator($this, $this->quotation))->prepare());
    }
}
