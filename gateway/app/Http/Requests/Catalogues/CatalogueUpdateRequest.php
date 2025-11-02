<?php

namespace App\Http\Requests\Catalogues;

use App\Enums\CalculationType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

/**
 * Class CatalogueUpdateRequest
 * @package App\Http\Requests\Catalogues
 * @OA\Schema(
 * )
 */
class CatalogueUpdateRequest extends FormRequest
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
     * @OA\Property(format="string", title="type", example="biotop", description="required|string", property="type"),
     * @OA\Property(format="int64", title="type_id", example="6541321", description="required|string|unique:catalogues,type_id,null,id,grs_id,{$this->grs_id}", property="type_id"),
     * @OA\Property(format="int64", title="grs", example="80", description="required|numeric", property="grs"),
     * @OA\Property(format="int64", title="grs_id", example="654231", description="required|string|unique:catalogues,grs_id,null,id,type_id,{$this->type_id}", property="grs_id"),
     * @OA\Property(format="string", title="width", example="420", description="required|numeric", property="width"),
     * @OA\Property(format="string", title="height", example="360", description="required|numeric", property="height"),
     * @OA\Property(format="string", title="depth", example="1", description="required|numeric", property="depth"),
     * @OA\Property(format="string", title="price", example="800", description="required|numeric", property="price"),
     * @OA\Property(format="string", title="calc_type", example="m2", description="required|string|in:kg,2m", property="calc_type"),
     */
    public function rules()
    {
        return [
            "supplier" => "nullable|string",
            "art_nr" => "required|string",
            "material" => "required|string",
            "material_link" => "nullable|string",
            "material_id" => "required|string",

            "sheet" => "required|boolean",
            "width" => "required|integer",
            "height" => "required_if:sheet,false|integer",

            "density" => "nullable|numeric|between:0,99.99",
            "length" => "nullable|integer",

            "grs" => "nullable|integer",
            "grs_link" => "nullable|string",
            "grs_id" => "required|string",
            "price" => "nullable|numeric",
            "ean" => "nullable|string",
            "calc_type" => ["required", "string",new Enum(CalculationType::class)],
        ];
    }

    protected function prepareForValidation()
    {
        $this->merge([
            'grs' => preg_replace("/\D/", '', $this->grs)
        ]);
    }
}
