<?php

namespace Modules\Cms\Foundation\Traits;

use App\Models\Tenants\Media\FileManager;
use Illuminate\Database\Eloquent\Casts\ArrayObject;
use Illuminate\Support\Collection;

trait HasRecursiveModels
{
    public function getObject($object, array $variables)
    {
        $newObject = $object;
        while (!empty($variables)) {
            $key = array_shift($variables);

            if (in_array($key, ['password', 'remember_token', 'two_factor_secret', 'two_factor_recovery_codes', 'ctx_id', 'ctx', 'created_from', 'reference'])) {
                $newObject = null;
                break;
            }

            if (is_array($newObject) || $newObject instanceof Collection || $newObject instanceof ArrayObject){
                $newObject = optional($newObject)[$key];
            } else {
                $newObject = optional($newObject)->{$key};
            }
        }

        if (is_array($newObject)) {
            return json_encode($newObject);
        } else if ($newObject instanceof ArrayObject) {
            return json_encode($newObject->toArray());
        }else if ($newObject instanceof FileManager) {
            return $this->getImageUrlFromFileManagerModel($newObject);
        } else {
            return $newObject;
        }
    }
}
