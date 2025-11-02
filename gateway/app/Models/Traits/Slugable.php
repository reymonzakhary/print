<?php


namespace App\Models\Traits;


use Illuminate\Support\Str;

trait Slugable
{
    /**
     * slugable
     */
    public static function boot()
    {
        parent::boot();

        static::creating(function ($model) {

            // get the slug of the latest created post
            $max = $model->latest('id')->where('name', $model->name)->skip(0)->value('slug');

            if (!$max) {
                $model->slug = Str::slug($model->name);
            } else if (strlen($max) >= 3 && $max[-2] === '-' && is_numeric($max[-1])) {
                $model->slug = preg_replace_callback('/(\d+)$/', function ($mathces) {
                    return Str::slug($mathces[1] + 1);
                }, $max);
            } else {
                $model->slug = Str::slug((string)$model->name . ' 1');
            }
        });
    }
}
