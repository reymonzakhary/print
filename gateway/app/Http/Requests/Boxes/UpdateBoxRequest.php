<?php

namespace App\Http\Requests\Boxes;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

/**
 * Class UpdateCustomBoxRequest
 * @package App\Http\Resources\Boxes
 * @OA\Schema(
 *     schema="UpdateCustomBoxRequest",
 *     title="Custom store Options Request"
 *
 * )
 */
class UpdateBoxRequest extends FormRequest
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
            'name' => ['max:200', Rule::unique('boxes')->where(function ($q) {
                return $q->where([
                    'name' => $this->name,
                    'iso' => $this->iso
                ]);
            })->ignore($this->box)],
            'slug' => 'string',
            'description' => 'string|max:255',
            'input_type' => 'string|in:single,multiple',
            'incremental' => 'boolean',
            'select_limit' => 'integer',
            'option_limit' => 'integer',
            'parent_id' => 'nullable|integer|exists:boxes,row_id',
            'sqm' => 'boolean',
            'sort' => 'integer',
            'iso' => 'string|required',
            'base_id' => 'nullable',
            'additional' => 'boolean',

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
        if ($this->parent_id === $this->box->row_id ||
            $this->box->children->whereIn('row_id', $this->parent_id)->count() > 0) {
            throw ValidationException::withMessages(['parent_id' =>
                __("The selected parent id is invalid, trying to connect to it self or child box.")
            ]);
        }
        $translation = !$this->translation || collect($this->translation)->count() === 0
            ? [] : collect($this->translation)
                ->reject(fn($t) => Str::lower($t['iso']) === app()->getLocale())->toArray();
        $slug = $this->name ? ['slug' => Str::slug($this->name)] : [];
        $this->merge(array_merge([
            'iso' => App::getLocale(),
            'input_type' => $this->input_type ?? 'single',
            'incremental' => $this->incremental ?? false,
            'additional' => $this->additional ?? false,
            'sqm' => $this->sqm ?? false,
            'base_id' => $this->parent_id ?: $this->box->row_id,
        ], $slug, $translation));
    }
}
