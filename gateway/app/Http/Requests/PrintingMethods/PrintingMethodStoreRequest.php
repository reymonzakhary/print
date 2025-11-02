<?php

namespace App\Http\Requests\PrintingMethods;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Class PrintingMethodStoreRequest
 * @package App\Http\Requests\PrintingMethods
 * @OA\Schema(
 * )
 */
class PrintingMethodStoreRequest extends FormRequest
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
     * @OA\Property(format="string", title="name", example="offset", description="required|string", property="name"),
     * @OA\Property(format="string", title="iso", example="en", description="required|string|exists:languages,iso", property="iso"),
     * @OA\Property(format="int64", title="sort", example="1", description="nullable", property="sort"),
     */
    public function rules()
    {
        return [
            'name' => 'required|string',
            'iso' => 'required|string|exists:languages,iso',
            'sort' => 'integer|nullable'
        ];
    }

    protected function prepareForValidation()
    {
        $this->merge([
            'iso' => app()->getLocale()
        ]);
    }
}
