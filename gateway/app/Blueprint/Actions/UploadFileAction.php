<?php

namespace App\Blueprint\Actions;

use App\Blueprint\Actions\Traits\HasReportingTraits;
use App\Blueprint\Actions\Traits\MediaHandlerTrait;
use App\Blueprint\Contract\BluePrintActionContract;
use File;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class UploadFileAction implements BluePrintActionContract
{
    use MediaHandlerTrait, HasReportingTraits;

    public function handle(mixed $request, mixed $data, $node = null, mixed $cart = null)
    {

        $upload = collect($request->files)->filter(function ($f) use ($data) {
            if (File::mimeType($f) === $data['input']['type']) {
                return $f;
            }
        })->first();
        $request->merge([
            'filesCollections' => []
        ]);
        if (optional($request)['UploadFileAction']) {
            if (!optional($request['UploadFileAction'])[$data['as']]) {
                $uploaded = $request['UploadFileAction'];
                $uploaded[$data['as']] = $this->Upload($upload, $cart['cart'], $data, $request);
                return $request->merge([
                    'UploadFileAction' => $uploaded
                ]);
            }
        } else {
            return $request->merge([
                'UploadFileAction' => [
                    $data['as'] => $this->Upload($upload, $cart['cart'], $data, $request)
                ]
            ]);
        }

    }

    public function Upload($file, $cart, $data, $request)
    {

        if ($file instanceof UploadedFile) {
            $folder = cleanName(rand(0000, 9999));
            $newName = cleanName($file->getClientOriginalName());
            $path = cleanName('temp/' . $folder . '/');

            $this->addMedia(
                $cart,
                $file,
                $path,
                'UploadFileAction-' . $data['as'],
                'local',
                $request->tenant->uuid,
                name: $newName
            );
            $res = [
                'disk' => 'local',
                'path' => $request->tenant->uuid . DIRECTORY_SEPARATOR . $path,
                'name' => $newName,
                'url' => asset('local/' . cleanName($request->tenant->uuid . DIRECTORY_SEPARATOR . $path . DIRECTORY_SEPARATOR . $newName)),
            ];
//            $this->createReport('Upload File Action', [
//                $data['as'] => $res
//            ], $request);
            return $res;

        }
    }
}
