<?php

namespace App\Http\Requests\Status;

use App\Enums\Status;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Enum;

class StoreStatusRequest extends FormRequest
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
    public function rules(
        Request $request
    )
    {
        return [
            'code' => ['required', 'integer', new Enum(Status::class)],
            'name' => 'string|required|unique:statuses,name',
            'description' => 'string'
        ];
    }

}
