<?php

namespace App\Http\Requests\DeliveryDay;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\App;

/**
 * Class DeliveryDaysRequest
 * @package App\Http\Requests\DeliveryDay
 * @OA\Schema(
 *     schema="DeliveryDaysRequest",
 *     title="Delivery Days Request"
 *
 * )
 */
class DeliveryDaysRequest extends FormRequest
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
     * @OA\Property(format="string", title="label", description="required", property="label" ,example="Day-3"),
     * @OA\Property(format="integer", title="days", description="required|numeric", example="3", property="days"),
     * @OA\Property(format="string", title="iso", description="string|exists:languages,iso", property="iso"),
     * @OA\Property(format="string", title="mode", description="required|string|in:percentage,fixed", example="fixed", property="mode"),
     * @OA\Property(format="integer", title="price", description="numeric", example="100", property="price"),
     */
    public function rules()
    {
        return [
            "label" => "required",
            "days" => "required|numeric",
            'iso' => 'string|exists:languages,iso',
            'mode' => 'required|string|in:percentage,fixed',
            'price' => 'numeric',
        ];
    }

    protected function prepareForValidation()
    {
        $this->merge(['iso' => App::getLocale()]);
    }
}
