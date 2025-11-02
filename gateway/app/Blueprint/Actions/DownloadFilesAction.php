<?php

namespace App\Blueprint\Actions;

use App\Blueprint\Actions\Traits\HasReportingTraits;
use App\Models\Tenants\CartVariation;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DownloadFilesAction
{
    use HasReportingTraits;

    public function handle(mixed $request, ?CartVariation $cart)
    {

        $request->merge([
            'filesCollections' => []
        ]);
        if ($media = $cart->userMedia($request->tenant->user->id)->get()) {
            $upload = [];
            $media
                ->filter(fn($str) => Str::startsWith($str['collection'], 'UploadFileAction-'))
                ->map(function ($file) use (&$upload, $request) {
                    $path = cleanName($request->tenant->uuid . DIRECTORY_SEPARATOR . $file['path'] . DIRECTORY_SEPARATOR);
                    $name = cleanName($file['name']);
                    if (!Storage::disk('local')->exists($path . $name)) {
                        cloneData(
                            $file['disk'],
                            $path . $name,
                            'local',
                            $path . $name
                        );
                    }
                    $key = Str::replace('UploadFileAction-', '', $file['collection']);
                    $f = [
                        'name' => $file['name'],
                        'disk' => 'local',
                        'path' => cleanName($path . '/'),
                        'url' => asset('local/' . cleanName($path . '/' . $file['name']))
                    ];
//                    $this->createReport('Download Files Action', $f, $request);
                    return $upload[$key] = $f;
                });
            $request->merge(['UploadFileAction' => $upload]);

        }
    }
}
