<?php

namespace App\Http\Requests\Settings;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Request;

/**
 * Class StoreSettingRequest
 * @package App\Http\Requests\Settings
 * @OA\Schema(
 * )
 */
class StoreSettingRequest extends FormRequest
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
     * @OA\Property(format="string", title="sort", default="0",example="1", description="nullable|integer", property="sort"),
     * @OA\Property(format="string", title="name", default="null",example="language", description="string|nullable", property="name"),
     * @OA\Property(format="string", title="key", default="null",example="language", description="string|nullable", property="key"),
     * @OA\Property(format="string", title="secure_variable", default="false",example="false", description="string|nullable", property="secure_variable"),
     * @OA\Property(format="string", title="data_type", default="null",example="string", description="string|nullable", property="data_type"),
     * @OA\Property(format="string", title="data_variable", default="en|ar",example="en|ar", description="string|nullable", property="data_variable"),
     * @OA\Property(format="string", title="multi_select", default="false",example="false", description="boolean|nullable", property="multi_select"),
     * @OA\Property(format="string", title="incremental", default="false",example="false", description="boolean|nullable", property="incremental"),
     * @OA\Property(format="string", title="namespace", default="general",example="general", description="required|string", property="namespace"),
     * @OA\Property(format="string", title="area", default="core",example="core", description="required|string", property="area"),
     * @OA\Property(format="string", title="lexicon", default="en",example="en", description="nullable|string", property="lexicon"),
     * @OA\Property(format="string", title="value", default="null",example="en", description="nullable", property="value"),
     * @OA\Property(format="int64", title="ctx_id", default="null",example="1", description="integer|exists:contexts,id|nullable", property="ctx_id"),
     */
    /**
     * Get the validation rules that apply to the request.
     *
     * @param Request $request
     * @return array
     */
    public function rules(
        Request $request
    )
    {
        return [
            'sort' => 'nullable|integer',
            'key' => 'string|nullable',
            'data_type' => 'string|nullable',
            'data_variable' => 'string|nullable',
            'secure_variable' => 'boolean|nullable',
            'multi_select' => 'boolean|nullable',
            'incremental' => 'boolean|nullable',
            'namespace' => 'required|string',
            'area' => 'required|string',
            'lexicon' => 'nullable|string',
            'value' => 'nullable',
            'ctx_id' => 'integer|exists:contexts,id|nullable'
        ];
    }

}
