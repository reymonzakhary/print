<?php

namespace App\Blueprint\Actions;

use App\Blueprint\Actions\Traits\HasReportingTraits;
use App\Blueprint\Actions\Traits\TrashCollectionTraits;
use App\Blueprint\Contract\BluePrintActionContract;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class CreateCopiesActions implements BluePrintActionContract
{

    use TrashCollectionTraits, HasReportingTraits;

    public function handle(mixed $request, mixed $data, $node = null, mixed $cart = null)
    {

        $pdf = data_get($request->toArray(), $data['input']['from']['pdf']);
        $numOfCopy = $data['input']['from']['copies'];
        $copies = [];
        $original = Storage::disk('local')->path($pdf['path'] . DIRECTORY_SEPARATOR . $pdf['name']);
        $copiedFiles = collect(range(1, $numOfCopy))->map(function ($copy) use ($pdf, $node, $request) {
            $newName = $copy . '-' . $pdf['name'];
            $this->addToTrash($request, 'local', $pdf['path'], $newName);
            if (!Storage::disk($pdf['disk'])->exists(cleanName($pdf['path'] . DIRECTORY_SEPARATOR . $newName))) {
                Storage::disk($pdf['disk'])->copy(
                    cleanName($pdf['path'] . DIRECTORY_SEPARATOR . $pdf['name']),
                    cleanName($pdf['path'] . DIRECTORY_SEPARATOR . $newName)
                );
            }
            return $this->addLayer(Storage::disk($pdf['disk'])->path(cleanName($pdf['path'] . DIRECTORY_SEPARATOR . $newName)), $node, $newName, $request);
        })->implode("~");
        $copiedFiles = $original . '~' . $copiedFiles;
        $mergedPath = Storage::disk($pdf['disk'])->path(cleanName($pdf['path']));
        $mergedFileName = time() . rand(00, 99) . '.pdf';
        $this->addToTrash($request, 'local', $pdf['path'], $mergedFileName);
        Artisan::call("pdf:merge {$copiedFiles} {$mergedPath}/$mergedFileName");


        $pdf['name'] = $mergedFileName;

//        $this->createReport('Create Copies Actions', $pdf, $request);
        $request->merge([
            'CreateCopiesActions' => $pdf
        ]);
    }

    public function addLayer($pdf, $node, $original_name, $request)
    {
        $path = $pdf;
        $name = cleanName($original_name);
        $layer = collect($node['assets'])->map(function ($asset, $key) use (&$path, &$name, $pdf, $request) {
            $oldName = $name;
            $name = $key . $name;
            $template = $this->{"from" . Str::ucfirst($asset['type'])}($asset, $request);
            $temp_path = Str::replace($oldName, $name, $path);
            $this->addToTrash($request, 'local', Str::replace('/' . $name, '', $path), $name);
            $page = (string)optional($asset)['page'];
            $position = optional($asset)['position'] ?? "background";
            Artisan::call("pdf:layer {$template} {$path} {$temp_path} {$position} {$page}");
            Storage::disk('local')->delete(cleanName(str::replace(storage_path('app'), '', $path)));
            $path = $temp_path;
        });
        $this->addToTrash($request, 'local', Str::replace('/' . $name, '', $path), $name);
        return $path;
    }

    public function fromStorage($asset, $request)
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
                    $asset['path'] .
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

        collect($asset['database']['selector'])->map(function ($obj) use ($row, &$where, &$replace, &$fallback) {
            if (!$obj['regex']) {
                $searchable = explode('$', $obj['cond']['expr']);
                //@todo has to be fixed on live with daynamic columns
                if (count($searchable) > 1) {
                    $replace[] = Str::upper($searchable[0] . '_' . $row[$searchable[1]]);
                    $fallback['replace'][] = Str::upper($obj['fallback']);
                } else {
                    $replace[] = Str::upper($searchable[0]);
                    $fallback['replace'][] = Str::upper($obj['fallback']);

                }

            } else {
                preg_match("/\[(.*?)\]/", $obj['cond']['expr'], $match);
                if (optional($match)[1]) {
                    $replace[] = Str::upper($row[$match[1]]);
                    $fallback['replace'][] = Str::upper($obj['fallback']);
                } else {
                    return $obj['cond']['expr'];
                }
            }
            $where .= "UPPER({$obj['cond']['column']}) = ?";
            $fallback['where'] .= "UPPER({$obj['cond']['column']}) = ?";
        });
        if ($template = app($className)->whereRaw($where, $replace)->first()) {
            $this->media = $template->media->first();
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
                $this->addToTrash($request, 'local', $request->tenant->uuid . '/' . $this->media->path, $this->media->name);
                $disk = 'local';
            }
            return Storage::disk($disk)
                ->path(cleanName($request->tenant->uuid . '/' . $this->media->path . '/' . $this->media->name));
            /**
             * Fix it will remove
             */
        } elseif ($template = app($className)->whereRaw($fallback['where'], $fallback['replace'])->first()) {
            $this->media = $template->media->first();
            $disk = $this->media->disk;
            if ($this->media->disk === $disk) {
                if (!Storage::disk('local')->exists($request->tenant->uuid . '/' . $this->media->path . '/' . $this->media->name))
                    cloneData(
                        $disk,
                        $request->tenant->uuid . '/' . $this->media->path . '/' . $this->media->name,
                        'local',
                        cleanName($request->tenant->uuid . '/' . $this->media->path . '/' . $this->media->name)
                    );
                $this->addToTrash($request, 'local', $request->tenant->uuid . '/' . $this->media->path, $this->media->name);
                $disk = 'local';
            }
            return Storage::disk($disk)
                ->path(cleanName($request->tenant->uuid . '/' . $this->media->path . '/' . $this->media->name));
        }


        throw ValidationException::withMessages([
            'template' => __('Template :name not found! ', ['name' => Str::upper($row['program'])])
        ]);
    }
}
