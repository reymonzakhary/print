<?php

namespace App\Foundation\Settings\Contracts;

use Illuminate\Support\Facades\Cache;

class SettingsContract
{

    public function getFrom($key = 'system')
    {
        return $this->{$key} . "Settings"();
    }

    public function userSetting()
    {

    }

    public function systemSettings()
    {

    }

    /**
     * @param $key
     * @param $value
     * @return mixed
     */
    public function add($key, $value): mixed
    {
        Cache::add($key, $value);
        return $value;
    }

    /**
     * @param $key
     * @return mixed
     */
    public function get($key): mixed
    {
        return Cache::get($key);
    }

    /**
     * @param $key
     * @return bool
     */
    public function exists($key): bool
    {
        return Cache::has($key);
    }

    /**
     * @param $key
     * @return bool
     */
    public function forget($key): bool
    {
        return Cache::forget($key);
    }

    /**
     * @param $key
     * @param $value
     * @return mixed
     */
    public function refresh($key, $value): mixed
    {
        if ($this->exists($key)) {
            $this->forget($key);
            $this->add($key, $value);
            return $value;
        }
        $this->add($key, $value);
        return $value;
    }
}
