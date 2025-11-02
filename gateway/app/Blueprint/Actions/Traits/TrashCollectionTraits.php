<?php

namespace App\Blueprint\Actions\Traits;

use Illuminate\Support\Str;

trait TrashCollectionTraits
{
    public function addToTrash(mixed $request, $disk, $path, $name, $type = 'file'): void
    {
        $filesCollections = $request->filesCollections;
        $path = Str::replace('/var/www/storage/app/public/', '', $path);
        $filesCollections[] = [
            'disk' => $disk,
            'path' => $path,
            'name' => $name,
            'type' => $type,
        ];
        $request->merge([
            "filesCollections" => $filesCollections
        ]);
    }
}
