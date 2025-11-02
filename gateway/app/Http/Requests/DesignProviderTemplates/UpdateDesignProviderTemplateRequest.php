<?php

namespace App\Http\Requests\DesignProviderTemplates;

use App\Facades\DesignProvider;
use App\Models\Tenants\DesignProvider as TenantsDesignProvider;
use App\Models\Tenants\DesignProviderTemplate;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;

class UpdateDesignProviderTemplateRequest extends FormRequest
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
        return array_merge([
            'name' => 'required|string|max:255|unique:design_provider_templates,name,'.$this->templateModel->id,
            'design_provider_id' => 'required|exists:design_providers,id',
        ], DesignProvider::validation($this->provider));
    }

    public function prepareForValidation()
    {
        $provider = TenantsDesignProvider::find($this->design_provider_id);
        $templateModel = DesignProviderTemplate::where('id', (int) $this->route('template'))->first();

        if (!$provider) {
            throw ValidationException::withMessages([
                'provider' => __('Not found.')
            ]);
        }

        $this->merge([
            'provider' => $provider,
            'templateModel' => $templateModel,
        ]);
    }
}
