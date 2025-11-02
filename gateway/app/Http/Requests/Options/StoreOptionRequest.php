<?php

namespace App\Http\Requests\Options;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

/**
 * Class StoreCustomOptionRequest
 * @package App\Http\Resources\Options
 * @OA\Schema(
 *     schema="StoreCustomOptionRequest",
 *     title="Custom store Options Request"
 *
 * )
 */
class StoreOptionRequest extends FormRequest
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
     * @param Request $request
     * @return array
     * @OA\Property(format="int64", title="id", default=1, description="id", property="id"),
     * @OA\Property(format="string", title="name", default="red", description="name", property="name"),
     * @OA\Property(format="string", title="description", default="red color", description="description", property="description"),
     * @OA\Property(format="string", title="box_id", default=1, description="box_id", property="box_id"),
     * @OA\Property(format="string", title="input_type", default="checkbox", description="input_type", property="input_type"),
     * @OA\Property(format="string", title="incremental_by", default=1, description="incremental_by", property="incremental_by"),
     * @OA\Property(format="string", title="min", default=6, description="min", property="min"),
     * @OA\Property(format="string", title="max", default=10, description="max", property="max"),
     * @OA\Property(format="string", title="width", default=20, description="width", property="width"),
     * @OA\Property(format="string", title="height", default=20, description="height", property="height"),
     * @OA\Property(format="string", title="length", default=15, description="length", property="length"),
     * @OA\Property(format="string", title="unit", default="cm", description="unit", property="unit"),
     * @OA\Property(format="string", title="display_price", default=true, description="display_price", property="display_price"),
     * @OA\Property(format="string", title="price", default=100, description="price", property="price"),
     * @OA\Property(format="string", title="margin_value", default=5, description="margin_value", property="margin_value"),
     * @OA\Property(format="string", title="margin_type", default="top", description="margin_type", property="margin_type"),
     * @OA\Property(format="string", title="discount_value", default=2, description="discount_value", property="discount_value"),
     * @OA\Property(format="string", title="discount_type", default="bottom", description="discount_type", property="discount_type"),
     * @OA\Property(format="string", title="price_switch", default=false, description="price_switch", property="price_switch"),
     * @OA\Property(format="string", title="sort", default=6, description="sort", property="sort"),
     * @OA\Property(property="properties",type="array" ,
     *       @OA\Items(
     *          @OA\Property(property="props", type="string", example=null),
     *          @OA\Property(type="array", property="template", @OA\Items(
     *              @OA\Property(property="mode", type="string", example="DesignProviderTemplate"),
     *              @OA\Property(property="id", type="int64", example=1, description="number came from desigProvider"),
     *          )),
     *          @OA\Property(property="validations", type="string", example="[]")
     *        )
     *     ),
     * @OA\Property(format="string", title="secure", default=true, description="secure", property="secure"),
     * @OA\Property(format="string", title="parent_id", default=2, description="parent_id", property="parent_id"),
     * @OA\Property(format="string", title="iso", default=50, description="iso", property="iso"),
     * @OA\Property(format="string", title="published", default=true, description="published", property="published"),
     * @OA\Property(format="string", title="created_by", default=2, description="created_by", property="created_by"),
     * @OA\Property(format="string", title="published_by", default=2, description="published_by", property="published_by"),
     * @OA\Property(format="string", title="published_at", default="today", description="published_at", property="published_at"),
     * @OA\Property(format="string", title="media", default="[]", description="media", property="media"),
     * @OA\Property(property="translation",type="array" ,
     *       @OA\Items(
     *          @OA\Property(property="iso", type="string", example="en"),
     *          @OA\Property(property="name", type="string", example="name"),
     *          @OA\Property(property="description", type="string", example="description")
     *        )
     *     ),
     */
    public function rules(
        Request $request
    )
    {
        return [
            'name' => ['required', 'string', Rule::unique('options')->where(function ($q) {
                return $q->where([
                    'name' => $this->name,
                    'box_id' => $this->box_id,
                    'iso' => $this->iso
                ]);
            })],
            'description' => 'nullable|string|max:255',
            'box_id' => 'integer|required|exists:boxes,row_id',
            'input_type' => 'string|in:text,radio,checkbox,number,color,date,datetime,email,file,image,month,
                password,range,tel,time,url,week,path',
            'incremental_by' => 'integer|nullable',
            'min' => 'integer|nullable',
            'max' => 'integer|nullable',
            'width' => 'integer|digits_between:1,11|nullable',
            'height' => 'integer|digits_between:1,11|nullable',
            'length' => 'integer|digits_between:1,11|nullable',
            'unit' => 'string|exists:units,short_name',
            'single' => 'required_if:input_type,file|boolean',
            'upto' => 'integer|required_if:single,false',
            'mime_type' => 'string|required_if:input_type,file|in:pdf,xls,xlsx',

            'margin_value' => 'integer|nullable',
            'margin_type' => 'in:fixed,percentage|nullable',
            'discount_value' => 'integer|nullable',
            'discount_type' => 'in:fixed,percentage|nullable',
            'price' => 'integer|nullable|min:0',

            'price_switch' => 'boolean',
            'sort' => 'integer',
            'secure' => 'boolean',
            'parent_id' => 'integer|nullable|exists:options,id',
            'iso' => 'required',
            'created_by' => 'required|exists:users,id',

            'media' => 'array|nullable',
            'media.*' => 'string|nullable',
            'properties' => 'array|required',
            'properties.validations' => 'array',
            'properties.template' => 'array|nullable',
            'properties.template.id' => 'integer|exists:design_provider_templates,id',
            'properties.template.mode' => 'string|in:DesignProviderTemplate',

            'properties.props' => 'array|nullable',
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
            'iso' => App::getLocale(),
            'created_by' => $user?->id,
            'input_type' => $this->input_type ?? 'radio',
            'single' => $this->single ?? true,
            'min' => $this->min ?? 0,
            'properties' => [
                'validations' => $this->properties['validations'] ?? [],
                'template' => $this->properties['template'] ?? [],
                'props' => optional($this->properties)['props'],
            ],
            'max' => $this->max ?? 0,
            'width' => $this->width ?? 0,
            'height' => $this->height ?? 0,
            'length' => $this->length ?? 0,
            'unit' => $this->unit ?? 'mm',
            'price_switch' => $this->price_switch ?? false,
            'secure' => $this->secure ?? false,
        ], $translation));
    }

}
