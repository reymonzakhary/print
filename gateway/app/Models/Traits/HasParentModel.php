<?php


namespace App\Models\Traits;


use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Str;

trait HasParentModel
{

    public function getMorphClass()
    {
        if ($this->morphClass !== null) {
            return $this->morphClass;
        }
        $morphMap = Relation::morphMap();
        $class = static::class;
        if (!empty($morphMap) && in_array($class, $morphMap, true)) {
            return array_search($class, $morphMap, true);
        }
        $classArray = explode('\\', $class);
        $className = array_pop($classArray);

        return Str::lower($className);
    }

}
