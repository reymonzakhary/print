<?php

namespace App\Blueprint\Actions;

use App\Blueprint\Contract\BluePrintActionContract;
use Illuminate\Support\Facades\Storage;

class SelectFileFromStorageAction implements BluePrintActionContract
{

    public function handle(mixed $request, mixed $data, $node = null, mixed $cart = null)
    {
        $request->merge([
            'SelectFileFromStorageAction' => [
                'type' => 'url',
                'value' => $this->getDownloadFileUrl($request->{$data['input']['key']}, $request)
            ]
        ]);
    }

    public function getDownloadFileUrl($files, $request)
    {
        $path = ($files['path'] !== '/') ? $path = $files['path'] : "/";
        return [
            'disk' => $files['disk'],
            'path' => cleanName($files['path']),
            'name' => cleanName($files['name']),
            'url' => Storage::disk($files['disk'])->url($request->tenant->uuid . $path . $files['name']),
        ];
    }
}
