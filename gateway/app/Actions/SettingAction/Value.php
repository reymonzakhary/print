<?php


namespace App\Actions\SettingAction;

use App\Models\Tenants\Media\FileManager;
use Carbon\Carbon;
use JsonException;

class Value
{
    /**
     * @param object $model
     * @return mixed
     */
    final public static function array(object $model)
    {
        if ($model->multi_select) {
            return $model->value ? explode(",", $model->value) : [];
        }
        return $model->value;
    }

    /**
     * @param object $model
     * @return mixed
     */
    final public static function objects(object $model)
    {
        return $model->value;
    }

    /**
     * @param object $model
     * @return mixed
     */
    final public static function timezone(object $model)
    {
        return $model->value;
    }

    /**
     * @param object $model
     * @return int
     */
    final public static function integer(object $model): int
    {
        return (int)$model->value;
    }

    /**
     * @param object $model
     * @return float
     */
    final public static function float(object $model): float
    {
        return (float)$model->value;
    }

    /**
     * @param object $model
     * @return string
     */
    final public static function datetime(object $model): string
    {
        return Carbon::parse($model->value)->format('y-m-d');
    }

    /**
     * @param object $model
     * @return string
     */
    final public static function string(object $model): ?string
    {
        if ($model->secure_variable) {
            return "******";
        }
        return $model->value;
    }

    /**
     *
     * @param object $model
     * @return bool
     */
    final public static function boolean(object $model): bool
    {
        return (bool)$model->value;
    }

    /**
     * @param object $model
     * @return array
     * @throws JsonException
     */
    final public static function json(object $model): array
    {
        return (array)json_decode($model->value, true, 512, JSON_THROW_ON_ERROR | JSON_THROW_ON_ERROR);
    }

    /**
     * @param object $model
     * @return array
     */
    final public static function image(object $model): ?array
    {
        if ($file = FileManager::where('id', $model->value)->first()) {
            return ['path' => ltrim($file->path . '/' . $file->name, '/')]; //FileManager::where('id', $model->value)->first();
        }
        return ['path' => null];
    }
}
