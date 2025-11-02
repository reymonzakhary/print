<?php

namespace App\Http\Requests\DesignProviderTemplates;

use App\Facades\DesignProvider;
use App\Models\Tenants\DesignProvider as TenantsDesignProvider;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\UploadedFile;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use ZipArchive;

class StoreDesignProviderTemplateRequest extends FormRequest
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
            'name' => [
                'required',
                'string', 'max:200',
                Rule::unique('design_provider_templates')->where(function ($query) {
                    return $query->where('design_provider_id', (int)$this->design_provider_id)
                        ->where('name', $this->name);
                })
            ],
            'design_provider_id' => 'required|integer|exists:design_providers,id',

        ], DesignProvider::validation(
            $this->provider
        ));
    }

    /**
     * @return void
     * @throws ValidationException
     */
    public function zipType(): void
    {
        $zip = new ZipArchive();
        $zipStatus = $zip->open($this->template);
        $filesInside = [];
        for ($i = 0; $i < $zip->count(); $i++) {
            if (preg_match("/.+\.(sh|bash|pdf|dmg|exe|indd|psd|eps|excel|csv)[?]?/", $zip->getNameIndex($i))) {
                throw ValidationException::withMessages([
                    'order' => __('The zip file is empty, Or has no valid files e.g. js,css,html,htm,jpg,png,svg,jpeg,gif')
                ]);
            }
            $filesInside[] = $zip->getNameIndex($i);
        }
        $this->merge([
            'template_type' => 'zip'
        ]);
        /**
         * validate empty zip
         */
        if (!count($filesInside)) {
            throw ValidationException::withMessages([
                'order' => __('The zip file is empty, Or has no valid files e.g. html htm css js')
            ]);
        }
    }

    public function pdfType()
    {
        $this->merge([
            'template_type' => 'pdf'
        ]);
        return $this;
    }

    protected function passedValidation()
    {

    }

    /**
     * @throws ValidationException
     */
    protected function prepareForValidation()
    {
        $user = auth()->user();
        $provider = TenantsDesignProvider::find($this->design_provider_id);

        if (!$provider) {
            throw ValidationException::withMessages([
                'provider' => __('Not found.')
            ]);
        }

        if ($this->template instanceof UploadedFile) {
            match ($this->template?->getClientMimeType()) {
                'application/zip', 'application/x-zip-compressed' => $this->zipType(),
                'application/pdf' => $this->pdfType(),
                default => throw ValidationException::withMessages([
                    'template' => __('We can\'t handle this type!')
                ])
            };
        }
        $this->merge([
            'created_by' => $user->id,
            'provider' => $provider
        ]);
    }


}
