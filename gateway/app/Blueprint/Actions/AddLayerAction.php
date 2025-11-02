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

class AddLayerAction implements BluePrintActionContract
{
    use TrashCollectionTraits, HasReportingTraits;

    public $media;

    public function handle(mixed $request, mixed $data, $node = null, mixed $cart = null)
    {
        try {
            $pdfs = data_get($request->toArray(), $data['input']['from']);
        } catch (Exception $e) {
            $request->merge([
                'AddLayerAction' => collect([])
            ]);
            return;
        }
        $res = [];
        $pdfs->map(function ($pdf) use ($node, &$res, $request) {
            $path = Storage::disk('local')->path(
                cleanName($pdf['path'] . DIRECTORY_SEPARATOR . $pdf['name'])
            );
            $name = cleanName($pdf['name']);

            $layer = collect($node['assets'])->map(function ($asset, $key) use (&$path, &$name, $pdf, $request) {
//                $this->createReport('Add layer action assets['.$key.']', $asset, $request);
                $oldName = $name;
                $name = $key . $name;
                if (optional($asset)['bilingual'] && $iso = optional($pdf)['language']) {
                    $asset['name'] = $iso . '_' . $asset['name'];
                    $asset['iso'] = $iso;
                }
                $template = $this->{"from" . Str::ucfirst($asset['type'])}($asset, $request, $pdf['row']);
                $temp_path = Str::replace($oldName, $name, $path);
//                $this->addToTrash($request, 'local', Str::replace('/'.$name, '', $path),$name);
                $page = (string)optional($asset)['page'];
                $position = optional($asset)['position'] ?? "background";

                Artisan::call("pdf:layer {$template} {$path} {$temp_path} {$position} {$page}");
                Storage::disk('local')->delete(str::replace(storage_path('app/public/'), '', $path));
                $path = $temp_path;
            });
            $newPath = cleanName(str::replace(storage_path('app/public/'), '', $path));

            //$this->addToTrash($request, 'local', Str::replace('/'.$name, '', $newPath),$name);
            $res[] = $pdf->merge([
                'name' => $name,
                'path' => Str::replace('/' . $name, '', $newPath),
                'disk' => 'local',
                'url' => asset('local/' . DIRECTORY_SEPARATOR . cleanName($newPath)),
            ]);
        });

        $request->merge(['AddLayerAction' => collect($res)]);
    }

    public function fromStorage($asset, $request, $row = [])
    {
        if ($asset['disk'] === 'tenancy') {
            if (!Storage::disk('local')->exists(cleanName($request->tenant->uuid . DIRECTORY_SEPARATOR . $asset['path'] . DIRECTORY_SEPARATOR . $asset['name']))) {
                cloneData(
                    $asset['disk'],
                    $request->tenant->uuid . DIRECTORY_SEPARATOR . $asset['path'] . DIRECTORY_SEPARATOR . $asset['name'],
                    'local',
                    cleanName($request->tenant->uuid . DIRECTORY_SEPARATOR . $asset['path'] . DIRECTORY_SEPARATOR . $asset['name'])
                );
            }
        }
        $this->addToTrash($request, 'local', $request->tenant->uuid . DIRECTORY_SEPARATOR . $asset['path'], $asset['name']);
        return Storage::disk('local')
            ->path(
                cleanName(
                    $request->tenant->uuid . DIRECTORY_SEPARATOR .
                    $asset['path'] . DIRECTORY_SEPARATOR .
                    $asset['name'])
            );
    }

    protected function fromDatabase($asset, $request, $row = [])
    {
        $className = 'App\\Models\\Tenants\\' . $asset['database']['model'];
        $where = "";
        $replace = [];
        $fallback = [
            'where' => '',
            'replace' => [],
        ];
        if (!$row) {
            throw ValidationException::withMessages(['pdf' => __('We could not find the row in the pdf')]);
        }
        collect($asset['database']['selector'])->map(function ($obj) use ($row, &$where, &$replace, &$fallback, $asset) {
            $iso = optional($asset)['bilingual'] && optional($asset)['iso'] ? $asset['iso'] . '_' : '';
            if (!$obj['regex']) {
                $searchable = explode('$', $obj['cond']['expr']);
                //@todo has to be fixed on live with daynamic columns
                if (count($searchable) > 1) {
                    $replace[] = $iso . Str::upper($searchable[0] . '_' . $row[$searchable[1]]);
                    $fallback['replace'][] = Str::upper($obj['fallback']);
                } else {
                    $replace[] = $iso . Str::upper($searchable[0]);
                    $fallback['replace'][] = Str::upper($obj['fallback']);

                }

            } else {
                preg_match("/\[(.*?)\]/", $obj['cond']['expr'], $match);
                if (optional($match)[1]) {
                    $replace[] = $iso . Str::upper(Str::replace($match[0], $row[strtolower($match[1])], $obj['cond']['expr']));
                    $fallback['replace'][] = Str::upper($obj['fallback']);
                } else {
                    return $obj['cond']['expr'];
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

        if ($template = app($className)->whereRaw($where, $replace)->first()) {
            if ($asset['database']['model'] === 'FileManager') {
                $this->media = $template;
            } else {
                $this->media = $template->media->first();
            }
            $disk = $this->media->disk;
            if ($this->media->disk === $disk) {
                if (!Storage::disk('local')->exists($request->tenant->uuid . '/' . $this->media->path . '/' . $this->media->name)) {
                    cloneData(
                        $disk,
                        $request->tenant->uuid . '/' . $this->media->path . '/' . $this->media->name,
                        'local',
                        cleanName($request->tenant->uuid . '/' . $this->media->path . '/' . $this->media->name)
                    );
                }
                $disk = 'local';
            }
            $this->addToTrash($request, 'local', $request->tenant->uuid . '/' . $this->media->path, $this->media->name);

            return Storage::disk($disk)
                ->path(cleanName($request->tenant->uuid . '/' . $this->media->path . '/' . $this->media->name));
            /**
             * Fix it will remove
             */
        } elseif ($template = app($className)->whereRaw($fallback['where'], $fallback['replace'])->first()) {
            if ($asset['database']['model'] === 'FileManager') {
                $this->media = $template;
            } else {
                $this->media = $template->media->first();
            }
            $disk = $this->media->disk;
            if ($this->media->disk === $disk) {
                if (!Storage::disk('local')->exists($request->tenant->uuid . '/' . $this->media->path . '/' . $this->media->name))
                    cloneData(
                        $disk,
                        $request->tenant->uuid . '/' . $this->media->path . '/' . $this->media->name,
                        'local',
                        cleanName($request->tenant->uuid . '/' . $this->media->path . '/' . $this->media->name)
                    );
                $disk = 'local';
            }
            $this->addToTrash($request, 'local', $request->tenant->uuid . '/' . $this->media->path, $this->media->name);
            return Storage::disk($disk)
                ->path(cleanName($request->tenant->uuid . '/' . $this->media->path . '/' . $this->media->name));
        }


        throw ValidationException::withMessages([
            'layer' => __('layer for :name not found! :msg', [
                'name' => Str::upper($row['program']),
                'msg' => current($replace)
            ])
        ]);
    }


}
