<?php

namespace App\Http\Requests\Warehouses;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Class UpdateWarehouseRequest
 * @package App\Http\Requests\UpdateWarehouseRequest
 * @OA\Schema(
 * )
 */
class UpdateWarehouseRequest extends FormRequest
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
     * @OA\Property(format="string", title="name",example="warehouses 1", description="name", property="name"),
     * @OA\Property(format="int64", nullable=true, title="sort",example="1", description="sort", property="sort"),
     * @OA\Property(format="string", title="description",example="description", description="description", property="description"),
     */
    public function rules()
    {
        return [
            'name' =>
                ['required', 'string', Rule::unique('warehouses', 'name')->ignore($this->warehouse)],
            'sort' => 'nullable|integer',
            'description' => 'nullable|string',
        ];
    }
}
