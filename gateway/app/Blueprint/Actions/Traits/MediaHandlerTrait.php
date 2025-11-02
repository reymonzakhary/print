<?php

namespace App\Blueprint\Actions\Traits;

use App\Foundation\Media\MediaType;
use App\Models\Tenants\Media\FileManager;
use File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\File\UploadedFile;

trait MediaHandlerTrait
{
    public function addMedia(
        $cart,
        $file,
        $path,
        $collectionName,
        $disk = 'local',
        $uuid = null,
        $user_id = null,
        $name = null
    )
    {
        $name = $name ?? $file->getClientOriginalName();
        if ($file instanceof UploadedFile) {
            if (!Storage::disk($disk)
                ->exists(
                    $uuid .
                    DIRECTORY_SEPARATOR .
                    $path .
                    DIRECTORY_SEPARATOR .
                    $name
                )) {
                if (Storage::disk($disk)->putFileAs(
                    $uuid . DIRECTORY_SEPARATOR . $path,
                    $file,
                    $name
                )) {
                    $media = new FileManager();
                    $media->user_id = $user_id ?? auth()->user()?->id;
                    $media->name = $name;
                    $media->disk = $disk;
                    $media->collection = $collectionName;
                    $media->type = $file->getClientMimeType();
                    $media->size = $file->getSize();
                    $media->group = MediaType::getGroupType($media->type);
                    $media->path = $path ?? '/';
                    $media->ext = File::extension($file->getClientOriginalName());
                    return $this->attachMedia($cart, $media, $user_id);
                }

                return $cart;

            }
            return $cart;
        }
    }

    public function attachMedia($cart, $media, $user_id = null)
    {
        if (!$cart->media()->where('media.id', $media->id)->exists()) {
            $cart->media()->save($media, [
                'user_id' => $user_id ?? auth()->user()?->id,
                'uuid' => Str::uuid(),
                'collection' => $media->collection,
                'size' => $media->size,
                'manipulations' => $media->manipulations ? json_encode($this->manipulations, JSON_THROW_ON_ERROR) : NULL,
                'custom_properties' => $media->customProperties ? json_encode($this->customProperties, JSON_THROW_ON_ERROR) : NULL
            ]);
        }
        return $cart;
    }
}
