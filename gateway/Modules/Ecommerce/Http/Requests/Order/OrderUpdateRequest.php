<?php

namespace Modules\Ecommerce\Http\Requests\Order;

use App\Events\Tenant\Order\UpdateOrderEvent;
use App\Models\Tenants\Order;
use App\Rules\Addresses\AddressBlongsToUserRule;
use App\Rules\Orders\ContextValidationRule;
use App\Rules\Orders\UserBelongsToContextRule;
use App\Rules\Orders\UserValidationRule;
use App\Validators\PrepareOrderValidator;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;

class OrderUpdateRequest extends FormRequest
{

    protected Model $order;

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
            'type' => 'boolean',
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
            'reference' => 'nullable|string|max:100',
            'st' => 'integer|exists:statuses,code',
            'delivery_multiple' => 'boolean|nullable',
            'delivery_pickup' => 'boolean|nullable',
            'payment_method',
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
        ];
    }

    /**
     * @return void
     * @throws ValidationException
     */
    protected function prepareForValidation()
    {
        $this->order = Order::where('id', $this->order)->first();
        if (!$this->order) {
            throw ValidationException::withMessages([
                'order' => __('The order you have requested is not available!')
            ]);
        }
        $this->replace((new PrepareOrderValidator($this, $this->order))->prepare());
    }
}


