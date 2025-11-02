<?php

namespace Modules\Campaign\Http\Requests;

use App\Foundation\Settings\Settings;
use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Modules\Campaign\Http\Validations\PrepareCampaignConfigValidation;

class StoreCampaignRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
//        dd($this->start_on);
        return [
            'name' => 'required|unique:campaigns|max:200',
            "description" => 'string',
            "start_on" => 'nullable|integer',
            "active" => 'required|boolean',
            "file" => 'file|mimes:xlsx,xls,csv|max:20480', // max 20 mb
            "config" => 'array',
            'created_by' => 'integer|exists:users,id'
        ];
    }

    /**
     *
     */
    protected function prepareForValidation()
    {
        $this->merge([
            'active' => !is_null($this->active) ? (bool)$this->active : (bool)Settings::defaultOnCreate(true),
            'created_by' => Auth::user()->id,
            'start_on' => is_null($this->start_on) || $this->start_on === 'null' || $this->start_on === '0' ? NULL : Carbon::create($this->start_on)->timestamp,
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
