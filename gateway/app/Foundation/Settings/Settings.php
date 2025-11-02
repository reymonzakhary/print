<?php


namespace App\Foundation\Settings;


use App\Foundation\Settings\Contracts\SettingsContractInterface;
use App\Models\Tenants\Setting;
use Illuminate\Support\Str;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class Settings
{
    /**
     * @param $method
     * @param $arguments
     * @return string|null
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public static function __callStatic(
        $method,
        $arguments
    ): ?string
    {
        $fallback = optional($arguments)[0] ?? null;

        $method = Str::snake($method);
        if ($tenant = tenant()) {
            $key = $tenant->uuid . $method;

            if (app(SettingsContractInterface::class)->exists($key)) {
                return app(SettingsContractInterface::class)->get($key);
            }

            if ($setting = Setting::where('key', Str::snake($method))->first()) {
                app(SettingsContractInterface::class)->add($key, $setting->value);
                return Str::lower($setting->value);
            }
        }

        return Str::lower($fallback);
    }
}
