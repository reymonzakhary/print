<?php

namespace App\Http\Requests\Warehouses\Locations;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Class UpdateLocationRequest
 * @package App\Http\Resources\UpdateLocationRequest
 * @OA\Schema(
 * )
 */
class UpdateLocationRequest extends FormRequest
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
     * @OA\Property(format="int64", title="warehouse_id", default=1, description="warehouse_id", property="warehouse_id"),
     * @OA\Property(format="int64", title="sort", default=1, description="sort", property="sort"),
     * @OA\Property(format="string", title="ean", default="0123456789012", description="ean", property="ean"),
     * @OA\Property(format="string", title="position", default="asdas-asdasd-ada", description="position", property="position"),
     */
    public function rules()
    {
        return [
            'warehouse_id' => 'required|exists:warehouses,id',
            'sort' => 'nullable|integer',
            'ean' => 'nullable|string|max:255',
            'position' => 'nullable|string|max:255',
        ];
    }

    protected function prepareForValidation()
    {
        $this->merge([
            'warehouse_id' => $this->warehouse_id ?? $this->warehouse->id
        ]);
    }
}
