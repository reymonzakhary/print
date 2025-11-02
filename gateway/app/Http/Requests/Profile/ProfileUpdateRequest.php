<?php

namespace App\Http\Requests\Profile;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Class ProfileUpdateRequest
 * @package App\Http\Requests\Profile
 * @OA\Schema(
 * )
 */
class ProfileUpdateRequest extends FormRequest
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
     * @OA\Property(format="string", title="first_name", example="ahmed", description="required|string|min:2|max:100", property="first_name"),
     * @OA\Property(format="string", title="last_name", default="null",example="hifny", description="required|string|min:2|max:100", property="last_name"),
     * @OA\Property(format="string", title="gender", default="null",example="male", description="in:male,female,other|required", property="gender"),
     * @OA\Property(format="string", title="dob", default="null",example="1993-9-26", description="date|nullable", property="dob"),
     * @OA\Property(format="string", title="bio", default="null",example="long text about me", description="string|nullable", property="bio"),
     * @OA\Property(format="string", title="custom_field", default="null",example="[{'dummy':'data'}]", description="array|nullable", property="custom_field"),
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'gender' => 'in:male,female,other|required',
            'dob' => 'date|nullable',
            'bio' => 'string|nullable',
            'custom_field' => 'array|nullable',
        ];
    }
}
