<?php

namespace App\Blueprint\Actions;


use App\Blueprint\Actions\Traits\HasReportingTraits;
use App\Blueprint\Actions\Traits\TrashCollectionTraits;
use App\Blueprint\Contract\BluePrintActionContract;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Spatie\PdfToText\Pdf;

class DeleteDataFromPdfAction implements BluePrintActionContract
{
    use TrashCollectionTraits, HasReportingTraits;

    protected $media = null;
    protected $pdf = null;

    public function handle(mixed $request, mixed $data, $node = null, mixed $cart = null)
    {


        $pdf = data_get($request->toArray(), $data['input']['from']);
        $request->merge([
            'DeleteDataFromPdfAction' => $this->getText($pdf)->getPdfData($request, $data)
        ]);
//        $this->createReport('Create Copies Actions', $request->DeleteDataFromPdfAction->toArray(), $request);
    }

    protected function getText($pdf)
    {

        /**
         * Fix it will remove
         */
//        Storage::disk($pdf['disk'])->get($pdf['path'].$pdf['name']);
        $this->pdf = new UploadedFile(
            cleanName(Storage::disk($pdf['disk'])->path(cleanName($pdf['path'] . '/' . $pdf['name']))), $pdf['name']
        );
        $this->text = (new Pdf())->setPdf($this->pdf)->text();
        return $this;
    }

    private function getPdfData($request, $data)
    {
        preg_match_all('/\[Student.?Nr:(?<student>[\s?\d]+)]\[Program:(?<program>[\s?\d]+)]\[Language:(?<language>\s?[A-Za-z]{2})]\[Page:(?<page>\s?\d+)]/i', $this->text, $matches);
        $replacer = $data['input']['replacer'];

        $data_rows = collect($matches['student'])->map(function ($s, $key) use ($matches, $replacer, $request, $data) {

            return [
                'page' => trim($matches['page'][$key]),
                'selector' => $replacer['args'],
                'pdfPage' => $key + 1
            ];
        })->groupBy('student')->map(function ($res) {
            $temPath = str_replace('/var/www/storage/app/public/', '', $this->pdf->getPath());
            $pdf = new UploadedFile(cleanName(Storage::disk('local')->path($temPath . '/' . $this->pdf->getClientOriginalName())), $this->pdf->getClientOriginalName());
            $dir = cleanName(Str::replace(storage_path('app/public/'), '', $this->pdf->getPath()));
            $getReplace = $this->getReplace($pdf, $res);

            $getReplace['name'] = $pdf->getClientOriginalName();
            $getReplace['disk'] = 'local';
            $getReplace['path'] = $dir;
            $getReplace['url'] = asset('local/' . $dir . '/' . $this->pdf->getClientOriginalName());
            return collect($getReplace);
        });
        return $data_rows;
    }


    protected function fromDb($row, $file, $request)
    {
        $className = 'App\\Models\\Tenants\\' . $file['model'];
        $this->row = $row;
        $where = "";
        $replace = [];
        /**
         * @todo delete when lockup is ready
         */


        if (!$row) {
            throw ValidationException::withMessages(['pdf' => __('We could not find the row in the pdf')]);
        }
//        $where = "UPPER(name) = ?";
//        $replace[] = Str::upper($row['program']);

        collect($file['selector'])->map(function ($column, $key) use ($row, &$where, &$replace, $request) {
            $where .= "UPPER({$key}) = ?";
            $searchable = explode('$', $column);
            //@todo has to be fixed on live with daynamic columns
            if (count($searchable) > 1) {
                $replace[] = Str::upper($searchable[0] . '_' . $row[$searchable[1]]);
            } else {
                $replace[] = Str::upper($row[$searchable[0]]);
            }
        })->toArray();

        if ($template = app($className)->whereRaw($where, $replace)->first()) {
            $this->media = $template->media->first();
            $disk = $this->media->disk;
            if ($this->media->disk === 'tenancy') {
                cloneData(
                    'tenancy',
                    $request->tenant->uuid . '/' . $this->media->path . '/' . $this->media->name,
                    'local',
                    cleanName($request->tenant->uuid . '/' . $this->media->path . '/' . $this->media->name)
                );
                $disk = 'local';
            }
            $this->addToTrash($request, 'local', $request->tenant->uuid . '/' . $this->media->path, $this->media->name);
            $path = Storage::disk($disk)
                ->path(cleanName($request->tenant->uuid . '/' . $this->media->path . '/' . $this->media->name));
            /**
             * Fix it will remove
             */
            $this->template = new UploadedFile($path, $this->media->name);
            return $this;
        }


        throw ValidationException::withMessages([
            'template' => __('Template :name not found! ', ['name' => Str::upper($row['program'])])
        ]);
    }

    protected function getReplace($path, $args)
    {
        $text = (new Pdf())->setPdf($path)->text();
        return $this->extractExpresion($text, $args);
    }

    protected function extractExpresion($text, $args)
    {
        $search = [];
        collect($args)->map(function ($v) use (&$search, &$replace, $text) {
            return collect($v['selector'])->map(function ($i) use (&$search, &$replace, $text, $v) {
                if ($i['type'] === 'regx') {
                    preg_match_all('/' . $i['key'] . '/i', $text, $keys);
                    foreach ($keys[$i['select']] as $v) {
                        if (in_array($v, $search)) continue;
                        $search[] = $v;
                    }


                } else {
                    if (!in_array($i['key'], $search)) {
                        $search[] = $i['key'];
                    }

                }


            });
        });

        return [
            'search' => $search,
        ];
    }
}
