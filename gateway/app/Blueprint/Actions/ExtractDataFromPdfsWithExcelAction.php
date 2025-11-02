<?php

namespace App\Blueprint\Actions;


use App\Blueprint\Actions\Traits\HasReportingTraits;
use App\Blueprint\Actions\Traits\TrashCollectionTraits;
use App\Blueprint\Contract\BluePrintActionContract;
use App\Blueprint\SnippetsCode\Snippet;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Spatie\PdfToText\Pdf;

class ExtractDataFromPdfsWithExcelAction implements BluePrintActionContract
{
    use TrashCollectionTraits, HasReportingTraits;

    protected $media = null;
    protected $pdf = null;

    public function handle(mixed $request, mixed $data, $node = null, mixed $cart = null)
    {
        $rows = data_get($request->toArray(), $data['input']['from']['xls']);
        if (!$rows) {
            throw ValidationException::withMessages([
                'rows' => _('We can\'t read rows correctly!'),
                'key' => 'Extract Data From Pdf With Excel'
            ]);
        }

        $pdf = data_get($request->toArray(), $data['input']['from']['pdf']);
        if (!$pdf) {
            throw ValidationException::withMessages([
                'pdf' => _('pdf was not upload correctly!'),
                'key' => 'Extract Data From Pdf With Excel'
            ]);
        }

        $request->merge([
            'ExtractDataFromPdfsWithExcelAction' => $this->getText($pdf)->getPdfData($request, $rows, $data)
        ]);
//        $this->createReport(
//            'Extract Data From Pdfs With Excel Action',
//            $request->ExtractDataFromPdfsWithExcelAction->toArray(),
//            $request
//        );
    }

    protected function getText($pdf)
    {
        /**
         * Fix it will remove
         */
//        Storage::disk($pdf['disk'])->get($pdf['path'].$pdf['name']);


        $this->pdf = new UploadedFile(
            cleanName(Storage::disk($pdf['disk'])->path($pdf['path'] . $pdf['name'])), $pdf['name']
        );
        $this->text = (new Pdf())->setPdf($this->pdf)->text();
        return $this;
    }

    private function getPdfData($request, $rows, $data)
    {
        preg_match_all('/\[Student.?Nr:(?<student>[\s?\w]+)]\[Program:(?<program>[\s?\w]+)]\[Language:(?<language>\s?[A-Za-z]{2})]\[Page:(?<page>\s?\d+)]/i', $this->text, $matches);
        $replacer = $data['input']['replacer'];
        $rows_sn = collect($rows)->pluck('student nr')->toArray();
        $rowExists = collect($matches['student'])->filter(fn($student) => !in_array(trim((string)$student), $rows_sn, true));

        if ($rowExists->count()) {
            throw ValidationException::withMessages(['pdf' => __('We could not :rows find the row in the pdf', ['rows' => $rowExists->implode(', ')])]);
        }

        $data_rows = collect($matches['student'])->map(function ($s, $key) use ($matches, $replacer, $rows, $request, $data) {
            $row = collect($rows)->first(fn($i) => (string)$i['student nr'] === trim($s));
            $r = current($replacer);
            return [
                'row' => $row,
                'student' => trim($matches['student'][$key]),
                'program' => trim($matches['program'][$key]),
                'language' => trim($matches['language'][$key]),
                'page' => trim($matches['page'][$key]),
                'selector' => collect($r['args'])->map(function ($i) use ($row, $data, $matches, $key) {
                    $i['value'] = $this->getValueExpresion($i['value'], $row, $data, trim($matches['language'][$key]));
                    return $i;
                })->toArray(),
                'pdfPage' => $key + 1
            ];
        })->groupBy('student')->map(function ($res) use ($request) {
            $pages = collect($res)->pluck('pdfPage')->sort()->implode('~');
            $rand = time() . rand(000, 999);
            Artisan::call('pdf:separate ' . $pages . ' ' . $this->pdf->getRealPath() . ' ' . $this->pdf->getPath() . '/' . $rand);
            $temPath = str_replace('/var/www/storage/app/public/', '', $this->pdf->getPath());
            //$this->addToTrash($request, 'local', $temPath,$rand.'.pdf');
            $pdf = new UploadedFile(cleanName(Storage::disk('local')->path($temPath . '/' . $rand . '.pdf')), $rand . '.pdf');
            $dir = cleanName(Str::replace(storage_path('app/public/'), '', $this->pdf->getPath()));


            $getReplace = $this->getReplace($pdf, $res);
            $getReplace['name'] = $pdf->getClientOriginalName();
            $getReplace['disk'] = 'local';
            $getReplace['path'] = $dir;
            $getReplace['url'] = asset('local/' . $dir . '/' . $rand . '.pdf');
//            $this->addToTrash($request, 'local', $getReplace['path'],$getReplace['name']);

            $getReplace['row'] = optional($res->first())['row'];
            return collect($getReplace);

        });
        return $data_rows;
    }

    /**
     * @todo  we can delete this method
     */

    protected function getValueExpresion($text, $row, $data, $lang = 'en')
    {
        $newText = $text;
        preg_match_all('/\[[!?A-Za-z0-9_\-\s]*\]/i', $text, $keys);
        collect(current($keys))->unique()->each(function ($match) use (&$pattern, $row, &$newText, $data, $lang) {
            if (preg_match('/\[!(\w+)\]/i', Str::lower($match), $snippet)) {
                $row['pdf_language'] = $lang;
                $r = app(Snippet::class)->{Str::lower($snippet[1])}($row, $data);
                $newText = Str::replace($match, $r, $newText) . ' ';
            } else {
                $r = optional($row)[Str::lower(Str::replace(['[', ']'], ['', ''], $match))] ?? null;
                $newText = Str::replace($match, $r, $newText) . ' ';
            }
        })->toArray();
        return $newText;
    }


    protected function getReplace($path, $args)
    {
        $text = (new Pdf())->setPdf($path)->text();
        return $this->extractExpresion($text, $args);
    }

    protected function extractExpresion($text, $args)
    {
        $search = [];
        $replace = [];
        collect($args)->map(function ($v) use (&$search, &$replace, $text) {
            return collect($v['selector'])->map(function ($i) use (&$search, &$replace, $text, $v) {

                if ($i['type'] === 'regx') {
                    preg_match_all('/' . $i['key'] . '/i', $text, $keys);
                    foreach ($keys[$i['select']] as $v) {
                        if (in_array($v, $search)) continue;
                        $search[] = $v;
                        $replace[] = $i['value'] . ' ';
                    }


                } else {
                    if (!in_array($i['key'], $search)) {
                        $search[] = $i['key'];
                        $replace[] = $i['value'] . ' ';
                    }

                }


            });
        });
        return [
            'search' => $search,
            'replace' => $replace,
            'language' => optional($args->first())['language']
        ];
    }
}
