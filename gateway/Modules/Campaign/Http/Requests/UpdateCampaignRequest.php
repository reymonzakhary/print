<?php

namespace Modules\Campaign\Http\Requests;

use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;
use Modules\Campaign\Http\Validations\PrepareCampaignConfigValidation;

class UpdateCampaignRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'name' => 'max:200|unique:campaigns,name,' . $this->campaign->id,
            "description" => 'string',
            "start_on" => 'nullable|string',
            "end_on" => 'string',
            "active" => 'boolean',
            "file" => 'file|mimes:xlsx,xls,csv|max:10240', // max 10 mb
            "config" => 'array'
        ];
    }

    /**
     *
     */
    protected function prepareForValidation()
    {
        $this->merge([
            'start_on' => is_null($this->start_on) || $this->start_on === 'null' ? NULL : Carbon::create($this->start_on)->timestamp,
            'config' => (new PrepareCampaignConfigValidation($this->config))->prepare()
        ]);
    }


    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }
}
