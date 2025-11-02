<?php

namespace App\Http\Requests\Brands;

use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

/**
 * Class UpdateBrandRequest
 * @package App\Http\Resources\Brands
 * @OA\Schema(
 *     schema="UpdateBrandRequest",
 *     title="Custom store Brands Request"
 *
 * )
 */
class UpdateBrandRequest extends FormRequest
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
     * @OA\Property(format="string", title="name", default="adidas", description="name", property="name"),
     * @OA\Property(format="string", title="description", default="description", description="description", property="description"),
     * @OA\Property(format="boolean", title="published", default=true, description="published", property="published"),
     * @OA\Property(format="int64", title="created_by", default=1, description="created_by", property="created_by"),
     * @OA\Property(format="int64", title="published_by", default=1, description="published_by", property="published_by"),
     * @OA\Property(format="string", title="published_at", default="2022-06-28T11:59:11.789201Z", description="published_at", property="published_at"),
     * @OA\Property(format="string", title="media", default="['image.jpg', 'image.jpg']", description="media", property="media"),
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
            'name' => [
                'string', Rule::unique('brands', 'slug')->ignore($this->brand),
                'max:200'
            ],
            'description' => 'string|max:255',
            'iso' => 'required',
            'published' => 'boolean',
            'created_by' => 'integer|exists:users,id',
            'published_by' => 'integer|exists:users,id',
            'published_at' => 'required_if:published,true',
            'slug' => 'nullable',
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
        $slug = $this->name ? ['slug' => Str::slug($this->name)] : [];
        $translation = !$this->translation || collect($this->translation)->count() === 0
            ? [] : collect($this->translation)
                ->reject(fn($t) => Str::lower($t['iso']) === app()->getLocale())->toArray();
        $published = (bool)$this->published !== null ? [
            'published_by' => match ((bool)$this->published) {
                true => $user?->id,
                false => null
            },
            'published_at' => match ((bool)$this->published) {
                true => Carbon::now(),
                false => null
            }] : [];
        $this->merge(array_merge([
            'iso' => App::getLocale(),
        ], $slug, $translation, $published));
    }
}
