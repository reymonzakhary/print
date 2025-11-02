<?php

namespace App\Http\Requests\Boxes;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

/**
 * Class StoreCustomBoxRequest
 * @package App\Http\Resources\Boxes
 * @OA\Schema(
 *     schema="StoreCustomBoxRequest",
 *     title="Custom store Options Request"
 *
 * )
 */
class BoxStoreRequest extends FormRequest
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
     * @OA\Property(format="string", title="name", default="color", description="name", property="name"),
     * @OA\Property(format="string", title="description", default="color", description="description", property="description"),
     * @OA\Property(format="string", title="input_type", default="", description="single||multiple", property="input_type"),
     * @OA\Property(format="string", title="incremental", default=true, description="incremental", property="incremental"),
     * @OA\Property(format="string", title="select_limit", default=5, description="select_limit", property="select_limit"),
     * @OA\Property(format="string", title="option_limit", default=5, description="option_limit", property="option_limit"),
     * @OA\Property(format="string", title="sqm", default=true, description="sqm", property="sqm"),
     * @OA\Property(format="string", title="sort", default=4, description="sort", property="sort"),
     * @OA\Property(format="string", title="iso", default="color", description="iso", property="iso"),
     * @OA\Property(format="string", title="media", default="color", description="media", property="media"),
     * @OA\Property(format="string", title="created_by", default=2, description="created_by", property="created_by"),
     * @OA\Property(format="string", title="parent_id", default=2, description="parent_id", property="parent_id"),
     * @OA\Property(property="translation",type="array" ,
     *       @OA\Items(
     *          @OA\Property(property="iso", type="string", example="en"),
     *          @OA\Property(property="name", type="string", example="name"),
     *          @OA\Property(property="description", type="string", example="description")
     *        )
     *     ),
     */
    public function rules()
    {
        return [
            'name' => ['required', Rule::unique('boxes')->where(function ($q) {
                return $q->where([
                    'name' => $this->name,
                    'iso' => $this->iso
                ]);
            }), 'max:200'],
            'description' => 'string|max:255',
            'input_type' => 'string|in:single,multiple',
            'incremental' => 'boolean',
            'select_limit' => 'integer',
            'option_limit' => 'integer',
            'parent_id' => 'nullable|integer|exists:boxes,row_id',
            'sqm' => 'boolean',
            'appendage' => 'boolean',
            'sort' => 'integer',
            'iso' => 'string|required',
            'media' => 'array|nullable',
            'media.*' => 'string|nullable',
            'created_by' => 'integer|exists:users,id',

            'translation' => 'nullable|array',
            'translation.*.iso' => 'string|exists:languages,iso',
            'translation.*.name' => 'nullable|string|max:200',
            'translation.*.description' => 'nullable|string|max:255',
        ];
    }

    /**
     *
     */
    protected function prepareForValidation()
    {
        $user = Auth::user();
        $translation = !$this->translation || collect($this->translation)->count() === 0
            ? [] : collect($this->translation)
                ->reject(fn($t) => Str::lower($t['iso']) === app()->getLocale())->toArray();
        $this->merge(array_merge([
            'iso' => Str::lower(App::getLocale()),
            'created_by' => $user?->id,
            'input_type' => $this->input_type ?? 'single',
            'incremental' => $this->incremental ?? false,
            'sqm' => $this->sqm ?? false,
            'appendage' => $this->appendage ?? false,
        ], $translation));
    }
}
