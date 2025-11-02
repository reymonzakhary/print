<?php

namespace App\Blueprint\Actions;

use App\Blueprint\Actions\Traits\HasReportingTraits;
use App\Blueprint\Actions\Traits\TrashCollectionTraits;
use App\Blueprint\Contract\BluePrintActionContract;
use Exception;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Imagick;
use ImagickException;

class AddSignatureAction implements BluePrintActionContract
{
    use TrashCollectionTraits, HasReportingTraits;

    public function handle(mixed $request, mixed $data, $node = null, mixed $cart = null)
    {
        $pdfs = data_get($request->toArray(), $data['input']['from']);
        $res = $pdfs->map(function ($pdf) use ($node, $data, $request) {
            if (!optional($pdf)['position']) {
                return $pdf;
            }
            $path = cleanName(Storage::disk($pdf['disk'])->path($pdf['path'] . DIRECTORY_SEPARATOR . $pdf['name']));
            $newPath = $pdf['path'] . DIRECTORY_SEPARATOR . 'separate' . rand(000, 999);
            $output_s = cleanName(Storage::disk($pdf['disk'])->path($newPath));
            Artisan::call("pdf:separate 0 {$path} " . $output_s . ' split');
//            $this->addToTrash($request, 'local', $output_s, '', 'dir');
            $pdf_files = [];
            collect(Storage::disk('local')->listContents($newPath))
                ->map(function ($file) use (&$pdf_files) {
                    if ($file['extension'] === 'pdf') {
                        $pdf_files [$file['filename']] = [
                            "path" => Storage::disk('local')->path($file['path']),
                            "filename" => $file['filename'],
                        ];
                    }
                    return null;
                });
            collect($pdf['position'])->map(function ($key) use ($node, $output_s, $newPath, &$pdf_files, $request, $pdf) {
                $pos = collect($node['assets'])->filter(fn($asset) => $asset['uses'] === $key['key'])->first();
                $template = $this->{'from' . Str::ucfirst($pos['type'])}($pos, $request, $pdf['row']);
                $geo = [];
                try {
                    $geo = new Imagick($template);
                } catch (ImagickException $e) {
                    throw ValidationException::withMessages([
                        'template' =>
                            __('We couldn\'t load the template ') . $e->getMessage()
                    ]);
                }
                $geo = $geo->getImageGeometry();
                $x = $key['value']['@x'];
                $y = $key['value']['@y'] - optional($geo)['height'];
                $page = $key['page'];
                $s_path = $output_s . DIRECTORY_SEPARATOR . $page . '.pdf';
//                $this->addToTrash($request, 'local', $output_s, $page.'.pdf');
                try {
                    if ($pos['resize']) {
                        // pdf:converter {signature} {output}
                        $name = rand(000, 999);
                        $s_out_path = $output_s . DIRECTORY_SEPARATOR . $name . '.pdf';
//                        $this->addToTrash($request, 'local', $output_s, $name . '.pdf');
                        Artisan::call("pdf:converter {$template} {$s_out_path} {$x} {$y}");
                        $template = $s_out_path;
                        $name = rand(000, 999);
                        $s_out_path = $output_s . DIRECTORY_SEPARATOR . $name . '.pdf';
                    }
                } catch (Exception $e) {
                    throw ValidationException::withMessages([
                        'resize' => _($e->getMessage() . ' => AddSignatureAction')
                    ]);
                }

//                $this->addToTrash($request, 'local', $output_s, $name.'.pdf');
                $pdf_files[$page] = [
                    "path" => $s_out_path,
                    "filename" => $name
                ];

                $page = (string)optional($pos)['page'];
                $position = optional($pos)['position'] ?? "background";
                Artisan::call("pdf:layer {$template} {$s_path} {$s_out_path} {$position} {$page}");

//                Artisan::call("pdf:layer {$template} {$s_path} {$s_out_path} " . $pos['position'] ?? 'background');
            });
            $merge_files = collect($pdf_files)->pluck('path')->implode('~');
            $f_name = rand(000, 999) . time() . ".pdf";
            $m_file_path = Str::replace($pdf['name'], $f_name, $path);
            //$this->addToTrash($request, 'local', Str::replace('/'.$f_name, '', $m_file_path), $f_name);
            Artisan::call("pdf:merge {$merge_files} {$m_file_path}");
            Storage::disk('local')->deleteDirectory($newPath);
            $pdf['name'] = $f_name;

            $pdf['url'] = asset('local/' . cleanName($pdf['path'] . DIRECTORY_SEPARATOR . $f_name));
            return $pdf;
        });


//        $this->createReport('Add Signature Action', [
//            $data['as'] => $res->first()
//        ], $request);

        $request->merge([
            'AddSignatureAction' => $res->first()
        ]);
    }

    public function fromStorage($asset, $request, $row = [])
    {
        if (!Storage::disk('local')->exists(cleanName($request->tenant->uuid . '/' . $asset['path'] . DIRECTORY_SEPARATOR . $asset['name']))) {
            try {
                cloneData(
                    $asset['disk'],
                    $request->tenant->uuid . '/' . $asset['path'] . DIRECTORY_SEPARATOR . $asset['name'],
                    'local',
                    cleanName($request->tenant->uuid . '/' . $asset['path'] . DIRECTORY_SEPARATOR . $asset['name'])
                );
            } catch (Exception $e) {
                throw ValidationException::withMessages([
                    'template' => __('Template :name not found! ', ['name' => Str::upper($row['program'])])
                ]);
            }

        }
        $this->addToTrash($request, 'local', $request->tenant->uuid . '/' . $asset['path'], $asset['name']);
        return Storage::disk('local')
            ->path(
                cleanName($request->tenant->uuid . '/' .
                    $asset['path'] .
                    $asset['name'])
            );
    }

    public function fromDatabase($asset, $request, $row)
    {
        $this->data = [
            'search' => [],
            'replace' => [],
        ];
        $fallback = [
            'where' => '',
            'replace' => [],
        ];
        $className = 'App\\Models\\Tenants\\' . $asset['database']['model'];
        $where = "";
        $replace = [];
        /**
         * @todo delete when lockup is ready
         */
//        $where = "UPPER(name) = ?";
//        $replace[] = Str::upper($row['program']);

        collect($asset['database']['selector'])->map(function ($obj) use ($row, &$where, &$replace, &$fallback) {
            if (!optional($obj)['regex']) {
                $replace[] = $row[Str::lower($obj['cond']['expr'])];
                $fallback['replace'][] = $obj['fallback'];
//                $fallback['replace'][] = Str::upper($obj['fallback']);

            } else {
                preg_match("/\[(.*?)\]/", $obj['cond']['expr'], $match);

                if (optional($match)[1]) {
                    $replace[] = Str::replace($match[0], $row[$match[1]], $obj['cond']['expr']);
//                    $replace[] = Str::upper(Str::replace($match[0], $row[$match[1]], $obj['cond']['expr']));
                    $fallback['replace'][] = Str::upper($obj['fallback']);
                } else {
                    return null;
                }
            }
            if (optional($obj['cond'])['multiple']) {
                $columns = collect($obj['cond']['columns'])->map(fn($col) => "UPPER({$col})")->implode(',');
                $where .= "CONCAT_WS('{$obj['cond']['separator']}', {$columns}) = ?";
            } else {
                $where .= "UPPER({$obj['cond']['column']}) = ?";
            }

            $fallback['where'] .= "UPPER({$obj['cond']['column']}) = ?";
        });
        if (!Storage::disk('local')->exists(cleanName($request->tenant->uuid . '/' . current($replace)))) {
            try {
                cloneData(
                    $asset['disk'],
                    $request->tenant->uuid . '/' . current($replace),
                    'local',
                    cleanName($request->tenant->uuid . '/' . current($replace))
                );
            } catch (Exception $e) {
                throw ValidationException::withMessages([
                    'template' => __('Template :name not found! ', ['name' => Str::upper($row['program'])])
                ]);
            }

        }
        $this->addToTrash($request, 'local', $request->tenant->uuid . '/' . $asset['path'], current($replace));

        if (Storage::disk('local')->exists(cleanName($request->tenant->uuid . '/' . current($replace)))) {
            return Storage::disk('local')
                ->path(cleanName($request->tenant->uuid . '/' . current($replace)));
        }

        throw ValidationException::withMessages([
            'template' => __('Template :name not found! ', ['name' => Str::upper($row['program'])])
        ]);
    }

}
