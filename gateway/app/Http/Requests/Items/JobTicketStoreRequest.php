<?php

namespace App\Http\Requests\Items;

use Illuminate\Foundation\Http\FormRequest;

class JobTicketStoreRequest extends FormRequest
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
            'iso' => 'string|exists:languages,iso',
            'format' => 'required|in:html,xml,pdf'
        ];
    }

    protected function prepareForValidation()
    {
        $this->merge([
            'format' => $this->get('format')??'xml',
            'iso' => $this->get('iso') ?? app()->getLocale()
        ]);
    }
}
