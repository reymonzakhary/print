<?php

namespace App\Http\Requests\Users;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Class UserCompanyStoreRequest
 * @package App\Http\Requests\Users
 * @OA\Schema(
 * )
 */
class UserCompanyStoreRequest extends FormRequest
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
     * @OA\Property(format="string", title="name", example="hifnico", description="required|string", property="name"),
     * @OA\Property(format="string", title="description", default="null",example="details about company", description="nullable|string|max:255", property="description"),
     * @OA\Property(format="string", title="email",example="hifny@gmail.com", description="email|max:255|nullable", property="email"),
     * @OA\Property(format="string", title="coc", default="null",example="123", description="string|nullable", property="coc"),
     * @OA\Property(format="string", title="tax_nr", default="null",example="1234", description="string|nullable", property="tax_nr"),
     * @OA\Property(format="string", title="url", default="null",example="https://google.com", description="active_url|url|nullable", property="url"),
     */
    public function rules()
    {
        return [
            'name' => 'required|string',
            'description' => 'nullable|string|max:255',
            'email' => 'email|max:255|nullable',
            'coc' => 'string|nullable',
            'tax_nr' => 'string|nullable',
            'url' => 'active_url|url|nullable'
        ];
    }
}
