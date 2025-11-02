<?php

namespace App\Http\Requests\Blueprints;


use App\Enums\BlueprintNamespaces;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class BlueprintUpdateRequest extends FormRequest
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
    public function rules()
    {
        return [
            'name' => 'required',
            'ns' => ['required', 'string', new Enum(BlueprintNamespaces::class)],
            'blueprint' => 'array',
            'configuration' => 'array',
            'sort' => 'integer'
        ];
    }

    /**
     *
     */
    protected function prepareForValidation(): void
    {
    }
}
