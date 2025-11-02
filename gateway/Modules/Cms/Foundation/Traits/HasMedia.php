<?php

namespace Modules\Cms\Foundation\Traits;

use App\Models\Tenants\Media\FileManager;
use Illuminate\Support\Facades\Storage;

trait HasMedia
{
    /**
     * return url from a file manager model
     * @param FileManager
     * @return string
     */
    private function getImageUrlFromFileManagerModel(FileManager $fm)
    {
        return Storage::disk($fm->disk)->url($this->getImagePathFromFileManagerModel($fm));
    }

    /**
     * takes the file manager model to generate the path for a media
     * remove multiple slashes from it
     * @param FileManager
     * @return string
     */
    private function getImagePathFromFileManagerModel(FileManager $fm)
    {
        $path = $fm->path;
        if (isset(request()->tenant) && isset(request()->tenant->uuid)) {
            $path = str_replace(request()->tenant->uuid, '', $path);
        }

        return preg_replace_callback( // to remove additional slashes / in path
            '/\/+/',
            fn () => '/',
            request()->tenant->uuid . '/' . $path . '/' . $fm->name
        );
    }
}
