<?php

namespace App\Http\Requests\Order;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

/**
 * Class QuotationStoreRequest
 * @package App\Http\Resources\QuotationStoreRequest
 * @OA\Schema(
 * )
 */
class QuotationStoreRequest extends FormRequest
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
    /**
     * @OA\Property(format="string", title="reference", default="1158", description="reference", property="reference"),
     * @OA\Property(format="int64", title="discount_id", default="1", description="discount_id", property="discount_id"),
     * @OA\Property(format="bool", title="type", default=false, description="type", property="type"),
     * @OA\Property(format="int64", title="st", default="300", description="st", property="st"),
     * @OA\Property(format="int64", title="user_id", default="1", description="user_id", property="user_id"),
     * @OA\Property(format="bool", title="delivery_multiple", default=false, description="delivery_multiple", property="delivery_multiple"),
     * @OA\Property(format="bool", title="delivery_pickup", default=false, description="delivery_pickup", property="delivery_pickup"),
     * @OA\Property(format="int64", title="shipping_cost", default="", description="shipping_cost", property="shipping_cost"),
     * @OA\Property(format="int64", title="cost", default="50", description="cost", property="cost"),
     * @OA\Property(format="int64", title="note", default="", description="note", property="note"),
     * @OA\Property(format="int64", title="ctx_id", default="1", description="ctx_id", property="ctx_id"),
     * @OA\Property(format="int64", title="expire_at", default="2022-04-04T09:22:28.000000Z", description="expire_at", property="expire_at"),
     * @OA\Property(format="int64", title="created_from", default="2022-04-04T09:22:28.000000Z", description="created_from", property="created_from"),
     */
    public function rules()
    {
        return [
            'reference' => 'string|max:100',
            'discount_id' => 'nullable|exists:discounts,id',
            'type' => 'boolean|required',
            'st' => 'integer|exists:statuses,code',
            'user_id' => 'integer|exists:users,id',
            'delivery_multiple' => 'boolean',
            'delivery_pickup' => 'boolean',
            'shipping_cost' => 'nullable|integer|min:1',
            'cost' => 'nullable|integer|min:1',
            'note' => 'string|min:3|max:255',
            'ctx_id' => 'integer|exists:contexts,id',
            'expire_at' => 'nullable',
            'created_from' => 'required',
            'message' => 'string|nullable'
        ];
    }

//
    protected function prepareForValidation()
    {
        $this->merge([
            'created_from' => Auth::user()->contexts()->first()->name,
            'type' => false
        ]);
    }
}
