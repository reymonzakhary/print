<?php

namespace App\Http\Requests\Users;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Class UpdateUserSettingRequest
 * @package App\Http\Requests\Users
 * @OA\Schema(
 * )
 */
class UpdateUserSettingRequest extends FormRequest
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
     * @OA\Property(format="string", title="name", default="null",example="language", description="string|nullable", property="name"),
     * @OA\Property(format="string", title="data_type", default="null",example="string", description="string|nullable", property="data_type"),
     * @OA\Property(format="string", title="namespace", default="general",example="general", description="required|string", property="namespace"),
     * @OA\Property(format="string", title="lexicon", default="en",example="en", description="nullable|string", property="lexicon"),
     * @OA\Property(format="string", title="value", default="null",example="en", description="nullable", property="value"),
     * @OA\Property(format="int64", title="ctx_id", default="null",example="1", description="integer|exists:contexts,id|nullable", property="ctx_id"),
     */
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'name' => 'string',
            'data_type' => 'string|nullable',
            'namespace' => 'string|unique:user_settings,namespace,' . $this->setting->key . ',key,lexicon,' . request()->input('lexicon') . ',key,' . request()->input('key'),
            'lexicon' => 'string|unique:user_settings,lexicon,' . $this->setting->key . ',key,key,' . request()->input('key') . ',namespace,' . request()->input('namespace'),
            'value' => 'required',
            'ctx_id' => 'integer|exists:contexts,id|nullable'
        ];
    }
}
