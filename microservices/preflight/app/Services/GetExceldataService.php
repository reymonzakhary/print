<?php

namespace App\Services;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\StudentImport;
use Illuminate\Http\Request;


/**
 * Class GetExceldataService.
 */
class GetExceldataService
{
    public function getExcelData(Request $request){

        /**
         * todo:: need this first sheet or not 
         */
        $array = Excel::toCollection(new StudentImport, $request->file('xlsx'))->first();

    return $array;
}
}
