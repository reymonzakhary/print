<?php


namespace App\Actions\SettingAction;


use App\Models\Tenant\Setting;
use App\Models\Tenant\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class SettingAction
{
    private array $settings = [];
    /**
     * @var User
     */
    private User $user;


    public function __construct(
        User   $user,
        string $build
    )
    {
        $this->user = $user;
        $this->{$build}();
    }

    final public function userSettingAction(): bool
    {
        collect($this->user->settings()->get())->map(function ($setting) {
            if ($set = Cache::store('redis')->get('settings.' . Str::camel($setting->key))) {
                $set === $setting->value ?
                    $this->settings['settings.' . Str::camel($setting->key)] = $set :
                    $this->settings['settings.' . Str::camel($setting->key)] = $setting->value;
            } else {
                $this->settings['settings.' . Str::camel($setting->key)] = $setting->value;
            }
        });

        return true;
    }


    final public function systemSettingAction(): self
    {
        collect(Setting::get())->map(function ($setting) {
            if ($set = Cache::store('redis')->get('settings.' . Str::camel($setting->key))) {
                $this->settings['settings.' . Str::camel($setting->key)] = $set;
            } else {
                $this->settings['settings.' . Str::camel($setting->key)] = $setting->value;
            }
        });
        return $this;
    }

    /**
     *
     */
    final public function build(): void
    {
        $this->systemSettingAction()->userSettingAction();
        collect($this->settings)->map(function ($value, $setting) {
            Cache::store('redis')->put($setting, $value);
        });
    }
}
