<?php

namespace App\Http\Requests\Acl;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Class StoreMediaToTeamRequest
 * @package App\Http\Requests\Acl
 * @OA\Schema(
 *     schema="StoreMediaToTeamRequest",
 *     title="Store Media To Team Request"
 *
 * )
 */
class StoreMediaToTeamRequest extends FormRequest
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
     * @OA\Property(format="string", title="media_sources", example="[1,2]" , property="media_sources"),
     */
    public function rules()
    {
        return [
            'media_sources' => 'required|array',
            'media_sources.*' => 'integer|exists:media_sources,id',
        ];
    }
}
