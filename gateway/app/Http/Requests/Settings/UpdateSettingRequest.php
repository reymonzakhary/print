<?php

namespace App\Http\Requests\Settings;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Class UpdateSettingRequest
 * @package App\Http\Requests\Settings
 * @OA\Schema(
 * )
 */
class UpdateSettingRequest extends FormRequest
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
    public function rules()
    {
        /**
         * @OA\Property(format="string", title="name", default="null",example="language", description="string|nullable", property="name"),
         * @OA\Property(format="string", title="data_type", default="null",example="string", description="string|nullable", property="data_type"),
         * @OA\Property(format="string", title="namespace", default="general",example="general", description="required|string", property="namespace"),
         * @OA\Property(format="string", title="lexicon", default="en",example="en", description="nullable|string", property="lexicon"),
         * @OA\Property(format="string", title="value", default="null",example="en", description="nullable", property="value"),
         * @OA\Property(format="int64", title="ctx_id", default="null",example="1", description="integer|exists:contexts,id|nullable", property="ctx_id"),
         */
        return [
            'name' => 'string',
            'description' => 'nullable|min:1|max:255',
            'input_type' => 'string',
            'namespace' => 'string|unique:settings,namespace,' . $this->setting . ',id,lexicon,' . request()->input('lexicon') . ',key,' . request()->input('key'),
            'lexicon' => 'string|unique:settings,lexicon,' . $this->setting . ',id,key,' . request()->input('key') . ',namespace,' . request()->input('namespace'),
            'value' => 'required',
            'ctx' => 'string',
        ];
    }
}
