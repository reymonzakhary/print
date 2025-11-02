<?php

namespace App\Models\Traits;

use App\Models\Tenants\Media\FileManager;
use Carbon\Carbon;
use JsonException;

trait HasDynamicValue
{
    /**
     * @param object $model
     * @param        $value
     * @return mixed
     */
    final public static function valueArray(object $model, $value)
    {
        if ($model->multi_select) {
            return implode(",", $value);
        }
        return $value;
    }

    /**
     * @param object $model
     * @param        $value
     * @return int
     */
    final public static function valueInteger(object $model, $value): int
    {
        return (int)$value;
    }

    /**
     * @param object $model
     * @param        $value
     * @return float
     */
    final public static function valueFloat(object $model, $value): float
    {
        return (float)$value;
    }

    /**
     * @param object $model
     * @param        $value
     * @return string
     */
    final public static function valueDatetime(object $model, $value): string
    {
        return Carbon::parse($value)->format('y-m-d');
    }

    /**
     * @param object $model
     * @param        $value
     * @return string
     */
    final public static function valueString(object $model, $value): ?string
    {
        if ($model->secure_variable) {

            return $value === "******" ? $model->value : $value;
        }
        return $value;
    }

    /**
     *
     * @param object $model
     * @param        $value
     * @return bool
     */
    final public static function valueBoolean(object $model, $value): bool
    {
        return (bool)$value;
    }

    /**
     * @param object $model
     * @param        $value
     * @return object
     * @throws JsonException
     */
    final public static function valueJson(object $model, $value): object
    {
        return (object)json_encode($value, JSON_THROW_ON_ERROR | true, 512);
    }

    /**
     * @param object $model
     * @param        $value
     * @return int|null
     * @throws JsonException
     */
    final public static function valueImage(object $model, $value): ?int
    {
        if ($value) {
            $params = explode('/', $value);
            $last = array_pop($params);
            $path = implode('/', $params);

            if ($file = FileManager::where('path', $path)->where('name', $last)->first()) {
                return $file->id;
            }
            return null;
        }

        return null;
    }
}
