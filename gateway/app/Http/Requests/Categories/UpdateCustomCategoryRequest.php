<?php

namespace App\Http\Requests\Categories;

use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

/**
 * Class UpdateCustomCategoryRequest
 * @package App\Http\Resources\Category
 * @OA\Schema(
 *     schema="UpdateCustomCategoryRequest",
 *     title="Custom Update Category Request"
 *
 * )
 */
class UpdateCustomCategoryRequest extends FormRequest
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
     * @OA\Property(format="string", title="name", default="shirt", description="name", property="name"),
     * @OA\Property(format="string", title="description", default="description", description="description", property="description"),
     * @OA\Property(format="string", title="parent_id", default="1", description="parent_id", property="parent_id"),
     * @OA\Property(format="boolean", title="sort", default=true, description="sort", property="sort"),
     * @OA\Property(format="int64", title="iso", default=1, description="iso", property="iso"),
     * @OA\Property(format="int64", title="margin_value", default=1, description="margin_value", property="margin_value"),
     * @OA\Property(format="int64", title="margin_type", default=1, description="margin_type", property="margin_type"),
     * @OA\Property(format="int64", title="discount_value", default=1, description="discount_value", property="discount_value"),
     * @OA\Property(format="int64", title="discount_type", default=1, description="discount_type", property="discount_type"),
     * @OA\Property(format="int64", title="published", default=1, description="published", property="published"),
     * @OA\Property(format="int64", title="created_by", default=1, description="created_by", property="created_by"),
     * @OA\Property(format="int64", title="published_by", default=1, description="published_by", property="published_by"),
     * @OA\Property(format="string", title="published_at", default="2022-06-28T11:59:11.789201Z", description="published_at", property="published_at"),
     * @OA\Property(format="string", title="media", default="['image.jpg', 'image.jpg']", description="media", property="media"),
     * @OA\Property(property="translation",type="array",
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
            'name' => [
                'string', Rule::unique('categories')->where(function ($q) {
                    return $q->where([
                        'name' => $this->name,
                        'iso' => $this->iso
                    ]);
                })->ignore($this->category)
            ],
            'description' => 'nullable|max:255',
            'parent_id' => 'nullable|integer|exists:categories,row_id',
            'sort' => 'integer|nullable',
            'reference' => 'nullable',
            'slug' => 'nullable',
            'brand_id' => 'nullable|integer|exists:brands,row_id',
            'base_id' => 'nullable',

            'margin_value' => 'integer|nullable',
            'margin_type' => 'in:fixed,percentage|nullable',
            'discount_value' => 'integer|nullable',
            'discount_type' => 'in:fixed,percentage|nullable',

            'published' => 'boolean',
            'published_by' => 'required',
            'published_at' => 'required',

            'translation' => 'nullable|array',
            'translation.*.iso' => 'string|exists:languages,iso',
            'translation.*.name' => 'nullable|string|max:200',
            'translation.*.description' => 'nullable|string|max:255',


        ];
    }

    protected function prepareForValidation()
    {
        if ($this->parent_id === $this->category->row_id ||
            $this->category->children->whereIn('row_id', $this->parent_id)->count() > 0) {
            throw ValidationException::withMessages(['parent_id' =>
                __("The selected parent id is invalid, trying to connect to it self or child category.")
            ]);
        }
        $translation = !$this->translation || collect($this->translation)->count() === 0
            ? [] : collect($this->translation)
                ->reject(fn($t) => Str::lower($t['iso']) === app()->getLocale())->toArray();
        $user = Auth::user();
        $published = $this->published !== null ? [
            'published_by' => match ($this->published) {
                true => $user?->id,
                false => null
            },
            'published_at' => match ($this->published) {
                true => Carbon::now(),
                false => null
            }] : [];
        $slug = $this->name ? ['slug' => Str::slug($this->name)] : [];
        $this->merge(array_merge([
            'iso' => App::getLocale(),
            'base_id' => $this->parent_id ?: $this->category->row_id,
        ], $slug, $translation, $published));
    }
}
