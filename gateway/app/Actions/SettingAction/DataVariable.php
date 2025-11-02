<?php


namespace App\Actions\SettingAction;


use App\Models\Tenants\Setting;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class DataVariable
{
    /**
     * @param Setting $model
     * @return array
     */
    final public static function array($model)
    {
        if ($model->data_model === 'local') {

            $class = "App\Actions\SettingAction\\" . Str::ucfirst(Str::camel($model->data_variable));
            if (class_exists($class)) {
                $class = new $class();
                return $class->get();
            }
        }
        return explode("||", $model->data_variable);
    }

    /**
     * @param Setting $model
     * @return Collection
     */
    final public static function objects($model): Collection
    {
        return collect(explode("||", $model->data_variable))->map(function ($object) {
            $result = [];
            $explode = explode(',', $object);
            array_walk($explode, function (&$value, $key) use (&$result) {
                [$k, $v] = explode(':', $value);
                $result[$k] = $v;
            });
            return $result;
        });
    }

    /**
     * @param $model
     * @return array|null
     */
    final public static function integer($model)
    {
        if ($model->data_variable) {
            return collect(explode("||", $model->data_variable))->map(function ($variable) {
                if (preg_match('/:/', $variable)) {
                    $sub = explode(':', $variable);
                    return [
                        $sub[0] => is_numeric($sub[1]) ? (int)$sub[1] : $sub[1]
                    ];
                }
                return $variable;
            })->toArray();
        }
        return null;
    }
}
