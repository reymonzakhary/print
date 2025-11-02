<?php

namespace App\Blueprint\Actions;

use App\Blueprint\Contract\BluePrintActionContract;

class CleanFilesActions implements BluePrintActionContract
{
    private array $actions = [
        'AddTextToPdfAction',
        'AddLayerAction',
        'ExtractDataFromPdfsAction',
        'ExtractDataFromPdfsWithExcelAction',
    ];

    public function handle(mixed $request, mixed $data, $node = null, mixed $cart = null)
    {

//        collect($this->actions)->map(function ($action) use($request){
//            if ($action = optional($request)[$action]){
//                $action->map(function ($act){
//                    if (Storage::disk('tenancy')->exists(cleanName($act['path'].DIRECTORY_SEPARATOR.$act['name']))){
//                        Storage::disk('tenancy')->delete(cleanName($act['path'].DIRECTORY_SEPARATOR.$act['name']));
//                    }
//                });
//            }
//        });
//        collect($request['UploadFileAction'])->map(function ($files){
//            Storage::disk('local')->deleteDirectory($files['path']);
//        });
    }
}
