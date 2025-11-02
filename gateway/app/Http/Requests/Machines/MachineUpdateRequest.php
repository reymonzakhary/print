<?php

namespace App\Http\Requests\Machines;

use App\Enums\MachineFed;
use App\Enums\MachineType;
use App\Enums\Units;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;
use Illuminate\Validation\ValidationException;

/**
 * Class MachineUpdateRequest
 * @package App\Http\Requests\Machines
 * @OA\Schema(
 * )
 */
class MachineUpdateRequest extends FormRequest
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
     * @OA\Property(format="string", title="title", example="title", description="title", property="title"),
     * @OA\Property(format="string", title="type", example="type", description="type", property="type"),
     * @OA\Property(format="string", title="model", example="model", description="model", property="model"),
     * @OA\Property(format="int64", title="description", example="description", description="description", property="description"),
     * @OA\Property(format="boolean", title="width", example="width", description="width", property="width"),
     * @OA\Property(format="int64", title="height", example="height", description="height", property="height"),
     * @OA\Property(format="int64", title="main", example="height", description="main", property="main"),
     * @OA\Property(format="boolean", title="active", example="active", description="boolean", property="active"),
     * @OA\Property(format="int64", title="thickness", example="thickness", description="boolean", property="thickness"),
     *
     * @OA\Property(format="boolean", title="calc_type", example="calc_type", description="calc_type", property="calc_type"),
     *
     * @OA\Property(format="string", title="price", example="en", description="price", property="price"),
     */
    public function rules()
    {
        return [
            "name" => "string",
            "description" => "string|max:255",
            "ean" => "string",
            'type' => [new Enum(MachineType::class)],
            'unit' => [new Enum(Units::class)],
            "width" => "integer",
            "height" => "integer",
            "wf" => "nullable|integer", // Percentage of waste during the printing process. e.g. 10 %
            "colors" => "nullable|array",
            "colors.*.mode_id" => "string",
            "colors.*.mode_name" => "string",
            "colors.*.speed" => "array",
            "colors.*.speed.mpm" => "nullable|integer",
            "colors.*.speed.spm" => "nullable|integer",
            "colors.*.spoilage" => "integer",

            "materials" => "nullable|array",
            "materials.*.mode_id" => "string",
            "materials.*.mode_name" => "string",
            "materials.*.speed" => "array",
            "materials.*.speed.mpm" => "integer",
            "materials.*.speed.spm" => "integer",

            "printable_frame_length_min" => "nullable|integer",
            "printable_frame_length_max" =>  "nullable|integer",

            "min_gsm" => "nullable|integer",
            "max_gsm" => "nullable|integer",

            "trim_area" => "nullable|integer",
            "trim_area_exclude_y" =>  "boolean",
            "trim_area_exclude_x" => "boolean",
            "margin_right" => "nullable|integer",
            "margin_left" => "nullable|integer",
            "margin_top" => "nullable|integer",
            "margin_bottom" => "nullable|integer",

            "attributes" => "nullable|array",
            "sqcm" => "required",
            "rpm" => "integer",
            "price" => "integer",
            "fed" => ["string", "required",new Enum(MachineFed::class)],
            "pm" => "required|string",
            "setup_time" => "integer",
            "cooling_time" => "integer",
            "cooling_time_per" => "integer",
            "mpm" => "nullable|integer",

            "options" => "array",
            "options.*.id" => "string",
            "options.*.sheet_runs.*.machine" => "string",
            "options.*.sheet_runs.*.dlv_production" => "array",
            "options.*.sheet_runs.*.dlv_production.*.days" => "integer|min:0",
            "options.*.sheet_runs.*.dlv_production.*.value" => "integer|min:0",
            "options.*.sheet_runs.*.dlv_production.*.mode" => "string|in:percentage,fixed",
            "options.*.sheet_runs.*.runs" => "array",
            "options.*.sheet_runs.*.runs.*.from" => "integer|min:0",
            "options.*.sheet_runs.*.runs.*.to" => "integer|min:0",
            "options.*.sheet_runs.*.runs.*.price" => "integer|min:0",

            'divide_start_cost' => 'boolean',
            'spoilage' => 'int' // dynamically calculated not a value from the request
        ];
    }

    /**
     * @return void
    */
    public function passedValidation()
    {
        $this->merge([
            'divide_start_cost' => (bool) $this->divide_start_cost,
        ]);
    }

    /**
     *
     * @return void
     * @throws ValidationException
     */
    public function prepareForValidation()
    {
        $area = $this->width * $this->height;

        $areaInCentimeters = match ($this->unit) {
            'mm' => $area / (10 ** 2),
            'point' => $area / (28.3465 ** 2),
            'cm' => $area,
            'inch' => $area / (0.393701 ** 2),
            default => throw ValidationException::withMessages([
                'unit' => [
                    __('invalid unit.')
                ]
            ]),
        };

        $this->merge([
            'sqcm' => $areaInCentimeters,
            'ean' => (string) $this->ean
        ]);
    }
}
