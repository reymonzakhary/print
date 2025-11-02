<?php

namespace App\Http\Requests\MediaSources;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Class MediaSourceRuleStoreRequest
 * @package App\Http\Resources\MediaSourceRuleStoreRequest
 * @OA\Schema(
 *     schema="MediaSourceRuleStoreRequest",
 *     title="Media Source Store Request Rule"
 * )
 */
class StoreMediaSourceRuleRequest extends FormRequest
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
     * @OA\Property(format="int64", title="user_id", default=1, description="user_id", property="user_id"),
     * @OA\Property(format="string", title="disk", default="tenants", description="disk", property="disk"),
     * @OA\Property(format="string", title="path", default="/file.png", description="path", property="path"),
     * @OA\Property(format="int64", title="access", default="[0,1,2]", description="access", property="access"),
     */
    public function rules()
    {
        return [
            'user_id' => "required_without:media_source_id|exists:users,id",
            'disk' => 'required|max:255',
            'path' => "required|string",
            'access' => 'required|integer|in:0,1,2',
        ];
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    protected function prepareForValidation()
    {
        $this->merge([
            'path' => request()->tenant->uuid . '/' . ltrim($this->path, '/'),
        ]);
    }

}
