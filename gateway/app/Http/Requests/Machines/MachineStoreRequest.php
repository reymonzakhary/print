<?php

namespace App\Http\Requests\Machines;

use App\Enums\MachineFed;
use App\Enums\MachineType;
use App\Enums\Units;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;
use Illuminate\Validation\ValidationException;

/**
 * Class MachineStoreRequest
 * @package App\Http\Requests\Machines
 * @OA\Schema(
 * )
 */
class MachineStoreRequest extends FormRequest
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
     * @OA\Property(format="string", title="machine_type", example="machine_type", description="machine_type", property="machine_type"),
     * @OA\Property(format="string", title="model", example="model", description="model", property="model"),
     * @OA\Property(format="int64", title="description", example="description", description="description", property="description"),
     * @OA\Property(format="boolean", title="width", example="width", description="width", property="width"),
     * @OA\Property(format="int64", title="height", example="height", description="height", property="height"),
     * @OA\Property(format="int64", title="rpm", example="rpm", description="rpm", property="rpm"),
     * @OA\Property(format="boolean", title="active", example="active", description="boolean", property="active"),
     * @OA\Property(format="boolean", title="calculation_build", example="calculation_build", description="calculation_build", property="calculation_build"),
     * @OA\Property(format="string", title="price", example="en", description="price", property="price"),
     */
    public function rules()
    {
        return [
            "name" => "required|string",
            "description" => "string|max:255",
            "ean" => "required|string",
            "type" => ["required", new Enum(MachineType::class)],
            "unit" => ["required", new Enum(Units::class)],
            "width" => "required|integer",
            "height" => "required|integer",

            "wf" => "nullable|integer", // Percentage of waste during the printing process. e.g. 10 %
            "colors" => "nullable|array",
            "colors.*.mode_id" => "string",
            "colors.*.mode_name" => "string",
            "colors.*.speed" => "array",
            "colors.*.speed.mpm" => "integer",
            "colors.*.speed.spm" => "integer",
            "colors.*.spoilage" => "integer",

            "materials" => "nullable|array",
            "materials.*.mode_id" => "string",
            "materials.*.mode_name" => "string",
            "materials.*.speed" => "array",
            "materials.*.speed.mpm" => "integer",
            "materials.*.speed.spm" => "integer",

            "printable_frame_length_min" => "nullable|integer",
            "printable_frame_length_max" =>  "nullable|integer",

            "min_gsm" => "integer",
            "max_gsm" => "integer",

            "trim_area" => "nullable|integer",
            "trim_area_exclude_y" =>  "boolean",
            "trim_area_exclude_x" => "boolean",
            "margin_right" => "nullable|integer",
            "margin_left" => "nullable|integer",
            "margin_top" => "nullable|integer",
            "margin_bottom" => "nullable|integer",

            "sqcm" => "required",
            "fed" => ["required","string", new Enum(MachineFed::class)],
            "spm" => "nullable|integer",
            "mpm" => "nullable|integer",
            "price" => "required|integer",
            "attributes" => "nullable|array",
            "setup_time" => "integer",
            "cooling_time" => "integer",
            "cooling_time_per" => "integer",
            "pm" => "required|string",
            "divide_start_cost" => "boolean",
            "spoilage" => "int" // dynamically calculated not a value from the request
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

        /**
         * @todo calculate spoilage
         */
    }

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
            'spoilage' => $this->spoilage === null? 0 : $this->spoilage,
            'sqcm' => $areaInCentimeters,
            'ean' => (string) $this->ean
        ]);
    }
}
