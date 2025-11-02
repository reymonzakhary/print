<?php

namespace App\Blueprint\Actions;

use App\Blueprint\Actions\Traits\HasReportingTraits;
use App\Blueprint\Contract\BluePrintActionContract;
use App\Services\PdfCo\PdfCoService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Smalot\PdfParser\Parser;

class GetPositionsAction implements BluePrintActionContract
{
    use HasReportingTraits;

    public PdfCoService $pdfCoService;

    public function __construct(
        PdfCoService $pdfCoService
    )
    {
        $this->pdfCoService = $pdfCoService;
    }

    public function handle(mixed $request, mixed $data, $node = null, mixed $cart = null)
    {
        $res = data_get($request->toArray(), $data['input']['from']);
        $res = ($res instanceof Collection) ? $res : collect([$res]);

        $request->merge([
            'GetPositionsAction' => $res->map(function ($student) use ($data, $request) {
//                $this->createReport('GetPositionsAction', is_array($student)?$student:$student->toArray(),$request);
                $path = Storage::disk('local')->path(cleanName($student['path'] . '/' . $student['name']));
                $parser = new Parser();
                $pdf = $parser->parseFile($path);
                $pages = $pdf->getPages();
                $search = $data['input']['search'];
                $search_keys = collect($data['input']['search'])
                    ->map(fn($o) => $o['key'])->toArray();

                $positions = [];

                foreach ($search_keys as $key) {
                    $k = collect($search)->first(fn($i) => strtolower($key) === strtolower($i['key']));
                    $positions = collect($pages)->map(fn($page, $index) => collect($page->getDataTm())
                        ->reject(fn($p) => !in_array($key, $p, true))
                        ->map(fn($p) => [
                            'key' => $k['as'],
                            'value' => [
                                '@x' => $p[0][4],
                                '@y' => $p[0][5],
                                '#text' => $p[1],
                            ],
                            'page' => $index + 1
                        ]))->reject(fn($p) => count($p->toArray()) === 0)->flatten(1)->toArray();
                }


                return collect($student)->merge([
                    'position' => $positions
                ]);
            })

        ]);
    }
}
