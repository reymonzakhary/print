<?php

namespace App\Actions;

use Lorisleiva\Actions\Concerns\AsAction;
use App\Services\GetExceldataService;
use Illuminate\Http\Request;


class GetExcelDataAction
{
    use AsAction;

    public function handle(Request $request)
    {
        $excelData=new GetExceldataService();
        $getexcelData=$excelData->getExcelData($request)->map(function($item, $key){
          return [$key=>$item];
        })->toArray();

        return $getexcelData;
    }
       
    
}
