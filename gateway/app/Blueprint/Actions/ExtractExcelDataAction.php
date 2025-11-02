<?php

namespace App\Blueprint\Actions;


use App\Blueprint\Contract\BluePrintActionContract;
use Illuminate\Support\Facades\Storage;
use Spatie\SimpleExcel\SimpleExcelReader;

class ExtractExcelDataAction implements BluePrintActionContract
{
//    use HasReportingTraits;
    public function handle(mixed $request, mixed $data, $node = null, mixed $cart = null)
    {
        $xls = data_get($request->toArray(), $data['input']['from']);
        $result = [];
//        Storage::disk('local')->put($xls['path'].$xls['name'], Http::get($xls['url'])->getBody());
        SimpleExcelReader::create(
            Storage::disk('local')->path(cleanName($xls['path'] . $xls['name']))
        )
            ->getRows()
            ->each(function ($row) use (&$result) {

                $result[$row['Student Nr']] = array_change_key_case($row);
            });
//        Storage::disk('local')->delete(cleanName($xls['path'].$xls['name']));
//        $this->createReport('Extract Excel Data Action', ['numOfRows' => count($result)],$request );
        $request->merge([
            'ExtractExcelDataAction' => $result,
            'numOfRows' => count($result)
        ]);
    }
}
