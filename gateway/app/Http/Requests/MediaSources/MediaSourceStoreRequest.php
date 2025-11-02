<?php

namespace App\Http\Requests\MediaSources;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Class MediaSourceStoreRequest
 * @package App\Http\Resources\MediaSourceStoreRequest
 * @OA\Schema(
 *     schema="MediaSourceStoreRequest",
 *     title="Media Source Store Request"
 * )
 */
class MediaSourceStoreRequest extends FormRequest
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
     * @OA\Property(format="string", title="name", default="MediaSourceResource", description="name", property="name"),
     * @OA\Property(format="int64", title="ctx_id", default=null, description="ctx_id", property="ctx_id"),
     */
    public function rules()
    {
        return [
            'name' => 'required|max:255|unique:media_sources',
            'ctx_id' => 'required|exists:contexts,id',
        ];
    }
}
