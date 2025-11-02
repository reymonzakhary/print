<?php

namespace App\Http\Requests\Tags;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Class UpdateTagRequest
 * @package App\Http\Requests\Tags
 * @OA\Schema(
 * )
 */
class UpdateTagRequest extends FormRequest
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
     * @OA\Property(format="string", title="name", default="null",example="example", description="required|string|max:191", property="name"),
     */
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'name' => 'required|string|max:191',
        ];
    }
}
