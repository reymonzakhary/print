<?php

declare(strict_types=1);

namespace App\Http\Requests\Plugins;

use Illuminate\Foundation\Http\FormRequest;

final class UpdateConfigurationRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'plugin__v' => ['sometimes', 'regex:/^\d+\.\d+\.\d+$/'], // Semantic versioning
            'plugin_name' => ['sometimes', 'string', 'min:5', 'max:50'], // String
            'plugin_port' => ['sometimes', 'integer', 'min:1', 'max:65535'], // Valid port range
            'plugin_routes' => ['sometimes', 'array'], // At least one route required
            'plugin_routes.*.route' => ['required', 'string', 'regex:/^[a-zA-Z0-9/{}/_-]+$/'], // Valid route format
            'plugin_routes.*.method' => ['required', 'string', 'in:GET,POST,PUT,DELETE,PATCH'], // Allowed HTTP methods
            'plugin_route_prefix' => ['sometimes', 'string', 'regex:/^[a-zA-Z0-9-]+$/'], // Valid prefix
            'plugin_external_endpoint' => ['sometimes', 'string'],
            'plugin_external_username' => ['sometimes', 'string'],
            'plugin_external_password' => ['sometimes', 'string'],
        ];
    }
}
