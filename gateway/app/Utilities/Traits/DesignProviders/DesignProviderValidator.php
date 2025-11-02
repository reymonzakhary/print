<?php

namespace App\Utilities\Traits\DesignProviders;

trait DesignProviderValidator
{
    /**
     * @return array
    */
    private function conneoPreflightValidations(): array
    {
        return [
            'sidesCount' => 'required|integer',
            'productSize' => 'array|required',
            'productSize.width' => 'required|integer',
            'productSize.height' => 'required|integer',
            'productType' => 'required',
            'bleed' => 'array|required',
            'bleed.left' => 'required|integer',
            'bleed.right' => 'required|integer',
            'bleed.top' => 'required|integer',
            'bleed.bottom' => 'required|integer',
            'quantity' => 'required|integer',
            'options' => 'array|required',
            'options.showBleedMarker' => 'required',
            'options.minimalDpi' =>     'required|integer',
        ];
    }

    /**
     * @return array
    */
    private function prindustryDesignToolValidations(): array
    {
        return [
            'description' => 'nullable|string|max:255',
            'settings' => 'array',
            'template' => 'required|file|mimes:zip,pdf|max:140240',
            'template_type' => 'required',
            'icon' => 'string|max:255|nullable',
            'type' => 'string|nullable',
            'locked' => 'boolean|nullable',
            'folder' => 'nullable|string',
            'properties' => 'json|nullable',
            'content' => 'string',
            'static' => 'boolean|nullable',
            'path' => 'string|nullable|max:255',
            'sort' => 'integer|nullable',
            'created_by' => 'required',
        ];
    }

}
