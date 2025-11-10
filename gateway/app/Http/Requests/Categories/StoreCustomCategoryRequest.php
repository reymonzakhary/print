<?php

namespace App\Http\Requests\Categories;

use App\Models\Tenant\Category;
use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

/**
 * Class StoreCustomCategoryRequest
 * @package App\Http\Resources\Brands
 * @OA\Schema(
 *     schema="StoreCustomCategoryRequest",
 *     title="Custom store Brands Request"
 *
 * )
 */
final class StoreCustomCategoryRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     * @OA\Property(format="string", title="name", default="shoes", description="name", property="name"),
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
     * @OA\Property(property="translation",type="array" ,
     *       @OA\Items(
     *          @OA\Property(property="iso", type="string", example="en"),
     *          @OA\Property(property="name", type="string", example="name"),
     *          @OA\Property(property="description", type="string", example="description")
     *        )
     *     ),
     */
    public function rules(): array
    {
        return [
            'name' => [
                'required', 'string',
                Rule::unique('categories')->where(function ($q) {
                    return $q->where([
                        'name' => $this->name,
                        'iso' => $this->iso
                    ]);
                })
            ],

            'description' => 'nullable|max:255',
            'parent_id' => 'nullable|integer|exists:categories,row_id',
            'sort' => 'integer|nullable',
            'iso' => 'required',

            'margin_value' => 'integer|nullable',
            'margin_type' => 'in:fixed,percentage|nullable',
            'discount_value' => 'integer|nullable',
            'discount_type' => 'in:fixed,percentage|nullable',

            'published' => 'boolean',
            'created_by' => 'integer|exists:users,id',
            'published_by' => 'integer|exists:users,id',
            'published_at' => 'required',

            'media' => 'array|nullable',
            'media.*' => 'string|nullable',

            'translation' => 'nullable|array',
            'translation.*.iso' => 'required|string|exists:languages,iso',
            'translation.*.name' => 'nullable|string|max:200',
            'translation.*.description' => 'nullable|string|max:255',
        ];
    }

    /**
     * @return void
     */
    protected function prepareForValidation(): void
    {
        $user = Auth::user();

        $translation = !$this->translation || collect($this->translation)->count() === 0
            ? [] : collect($this->translation)
                ->reject(fn($t) => Str::lower($t['iso']) === app()->getLocale())->toArray();

        $this->merge(array_merge([
            'iso' => App::getLocale(),
            'created_by' => $user?->id,
            'published' => $this->published ?? false,
            'published_by' => match ($this->published) {
                null, true => $user?->id,
                false => null
            },
            'published_at' => match ($this->published) {
                null, true => Carbon::now(),
                false => null
            }
        ], $translation));
    }

    /**
     * @throws ValidationException
     */
    protected function passedValidation(): void
    {
        if ($this->exists('translation')) {
            $this->ensureThatEachTranslationIsUnique($this->get('translation'));
        }
    }

    /**
     * Validate that the given translations are unique and does not exist in the database
     *
     * @param array $translations
     *
     * @return void
     *
     * @throws ValidationException
     */
    private function ensureThatEachTranslationIsUnique(
        array $translations
    ): void
    {
        foreach ($translations as $index => $translation) {
            if (
                isset($translation['name']) &&
                $this->isTranslationObjectExistInDatabase($translation['name'], $translation['iso'])
            ) {
                throw ValidationException::withMessages([
                    sprintf('translation.%s', $index) => __(
                        'Name ":name" with the iso ":iso" is already exist', [
                            'name' => $translation['name'],
                            'iso' => $translation['iso']
                        ]
                    )
                ]);
            }
        }
    }

    /**
     * Check if a translation object is unique or not
     *
     * @param string $name
     * @param string $iso
     *
     * @return bool
     */
    private function isTranslationObjectExistInDatabase(
        string $name,
        string $iso
    ): bool
    {
        return Category::query()->where(['name' => $name, 'iso' => $iso])->exists();
    }
}
