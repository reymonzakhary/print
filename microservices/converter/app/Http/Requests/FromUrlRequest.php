<?php

namespace App\Http\Requests;

use Anik\Form\FormRequest;
use Illuminate\Support\Facades\App;

class FromUrlRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize():bool
    {
        return true;
    }

    public function rules():array
    {

        return [
            'url'   => 'required|active_url',
            'type'  => 'required|in:pdf,png,jpg',
            'meme'  => 'required',
            'name'  => 'required|string',
            'options' => 'required|array',
            'options.*'=> 'boolean|in:fullPage,hideBackground,noSandbox',
            'width' => 'nullable|integer',
            'height' => 'nullable|integer',
            'quality' => 'required|integer|min:0|max:100',
            'deviceScaleFactor' => 'nullable|integer',
            'format' => "nullable|in,a0,a1,a2,a3,a4,a5,a6,a7"
        ];
    }

    protected function attributes():array
    {
        $type = $this->type??"pdf";
        $quality = $this->quality??100;
        $meme = (strtolower($type)==="pdf")?"application/pdf":"image/".strtolower($type);
        return $this->merge(
            [
                "type"=>$type,
                "meme"=>$meme,
                "quality"=>$quality,
            ]
        )->toArray();
    }
}
