<?php

namespace App\Foundation\Settings;

use App\Foundation\Settings\Contracts\SettingAbstractContract;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Setting extends SettingAbstractContract
{
    /**
     * @param $driver
     * @return Setting
     */
    public function from(
        $driver
    ): Setting
    {
        $this->driver = $driver;
        $this->model_name = match($driver) {
            'user' => auth()->user() ? \App\Models\Tenants\UserSetting::class: \App\Models\Tenants\Setting::class,
            default => \App\Models\Tenants\Setting::class
        };

        return $this;
    }

    /**
     * @param ...$columns
     * @return Setting
     */
    public function select(
        ...$columns
    ): Setting
    {
        $this->columns = $columns;
        return $this;
    }

    /**
     * @param $method
     * @param $parameters
     * @return Builder|Model|object|null
     */
    public function __call(
        $method,
        $parameters
    )
    {
        $this->fallback['value'] = collect($parameters)->first();
        return $this->{Str::snake($method)};
    }

}
