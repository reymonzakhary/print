<?php

namespace Modules\Cms\Foundation\Traits;

use Illuminate\Support\Facades\Storage;

trait HasProductImages
{
    public function formatProductImages($item)
    {
        return [
            'url' => $this->getImageUrlFromFileManagerModel($item),
            'type' =>  $item->type,
            'ext' => $item->ext
        ];
    }

    private function getImageUrlFromFileManagerModel($fm)
    {
        return Storage::disk($fm->disk)->url($this->getImagePathFromFileManagerModel($fm));
    }

    private function getImagePathFromFileManagerModel($fm)
    {
        $path = $fm->path;
        if (isset(request()->tenant) && isset(request()->tenant->uuid)) {
            $path = str_replace(request()->tenant->uuid, '', $path);
        }
        return str_replace('//', '/', request()->tenant->uuid . '/' . $path . '/' . $fm->name);
    }

    protected function renderProductImages($fm, $chunk)
    {
        $chunk = str_replace('[[+url]]', $this->getImageUrlFromFileManagerModel($fm), $chunk);
        $chunk = str_replace('[[+type]]', $fm->type, $chunk);
        $chunk = str_replace('[[+ext]]', $fm->ext, $chunk);
        
        return $chunk;
    }
}
