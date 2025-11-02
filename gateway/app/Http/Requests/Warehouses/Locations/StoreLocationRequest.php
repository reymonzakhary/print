<?php

namespace App\Http\Requests\Warehouses\Locations;

use App\Foundation\Settings\Settings;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Class StoreLocationRequest
 * @package App\Http\Resources\StoreLocationRequest
 * @OA\Schema(
 * )
 */
class StoreLocationRequest extends FormRequest
{
    public string $settings;

    public function __construct(array $query = [], array $request = [], array $attributes = [], array $cookies = [], array $files = [], array $server = [], $content = null)
    {
        $this->getSettings();
        parent::__construct($query, $request, $attributes, $cookies, $files, $server, $content);

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

    public function getSettings()
    {
        $this->settings = Settings::layoutWarehouse();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     * @OA\Property(format="int64", title="warehouse_id", default=1, description="warehouse_id", property="warehouse_id"),
     * @OA\Property(format="int64", title="sort", default=1, description="sort", property="sort"),
     * @OA\Property(format="string", title="ean", default="0123456789012", description="ean", property="ean"),
     * @OA\Property(format="string", title="position", default="asdas-asdasd-ada", description="position", property="position"),
     */
    public function rules()
    {
        return array_merge([

            'sort' => 'nullable|integer',
            'ean' => 'nullable|string|max:255',
            'position' => 'array',
        ], collect(explode(',', $this->settings))
            ->map(fn($column) => ["position.{$column}" => 'nullable|string|max:15'])
            ->flatMap(fn($i) => $i)->toArray());
    }

    protected function prepareForValidation()
    {
        $this->merge([
            'position' => collect($this->position)->map(fn($v, $k) => [$k => "{$k}-{$v}"])->flatMap(fn($i) => $i)->toArray()
        ]);
    }
}
