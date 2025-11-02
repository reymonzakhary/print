<?php

namespace App\Blueprint\Actions;

use App\Blueprint\Actions\Traits\TrashCollectionTraits;
use App\Blueprint\Contract\BluePrintActionContract;
use App\Blueprint\SnippetsCode\Snippet;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Spatie\PdfToText\Pdf;

class ExtractDataFromPdfsAction implements BluePrintActionContract
{
    use TrashCollectionTraits;

//        HasReportingTraits;
    protected $media = null;
    protected array $row;
    protected string $text;
    protected array $template;
    protected string $template_url;
    protected array $data = [
        'search' => [],
        'replace' => [],
    ];

    public function handle(mixed $request, mixed $data, $node = null, mixed $cart = null)
    {
        $xls = data_get($request->toArray(), $data['input']['from']);
        if (!$xls) {
            throw ValidationException::withMessages([
                'rows' => _('We can\'t read rows correctly!'),
                'key' => 'Extract Data From Pdfs Action'
            ]);
        }
        $request->merge(['ExtractDataFromPdfsAction' => collect($xls)->map(function ($row) use ($request, $data) {
//            $this->createReport('Extract Data From Pdfs Action', $row, $request);
            return $this->{"from" . Str::ucfirst($data['input']['template']['from'])}($row, $data['input']['template'], $request)
                ->getText($row, $request)
                ->ExtractExpresion($row, $data)
                ->toArray($row);
        })]);

    }

    protected function fromRequest($row, $file, $request)
    {
        return $this;
    }

    protected function fromDb($row, $file, $request)
    {
        $this->data = [
            'search' => [],
            'replace' => [],
        ];
        $fallback = [
            'where' => '',
            'replace' => [],
        ];
        $className = 'App\\Models\\Tenants\\' . $file['model'];
        $this->row = $row;
        $where = "";
        $replace = [];
        /**
         * @todo delete when lockup is ready
         */
//        $where = "UPPER(name) = ?";
//        $replace[] = Str::upper($row['program']);

        collect($file['selector'])->map(function ($obj) use ($row, &$where, &$replace, &$fallback) {
            if (!optional($obj)['regex']) {
                $replace[] = Str::upper($obj['cond']['expr']);
                $fallback['replace'][] = Str::upper($obj['fallback']);

            } else {
                preg_match("/\[(.*?)\]/", $obj['cond']['expr'], $match);

                if (optional($match)[1]) {
                    $replace[] = Str::upper(Str::replace($match[0], $row[$match[1]], $obj['cond']['expr']));
                    $fallback['replace'][] = Str::upper($obj['fallback']);
                } else {
                    return null;
                }
            }
            $where .= "UPPER({$obj['cond']['column']}) = ?";
            $fallback['where'] .= "UPPER({$obj['cond']['column']}) = ?";
        });

        if ($template = app($className)->whereRaw($where, $replace)->with('media')->first()) {
            $this->media = $template->media->first();
            return $this;
        } elseif ($template = app($className)->whereRaw($fallback['where'], $fallback['replace'])->with('media')->first()) {
            $this->media = $template->media->first();
            return $this;
        }

        throw ValidationException::withMessages([
            'template' => __('Template :name not found! ', ['name' => Str::upper($row['program'])])
        ]);
    }

    protected function getText($row, $request)
    {
        $clearName = cleanName($request->tenant->uuid . '/' . $this->media->path . '/' . $this->media->name);
        $url = Storage::disk($this->media->disk)
            ->url($request->tenant->uuid . '/' . $this->media->path . '/' . $this->media->name);
//        $this->addToTrash($request, 'local', $request->tenant->uuid . '/'.$this->media->path, $this->media->name);
        if (!Storage::disk('local')->exists($clearName)) {
            cloneData(
                $this->media->disk,
                $request->tenant->uuid . '/' . $this->media->path . '/' . $this->media->name,
                'local',
                $clearName);
        }
        /**
         * Fix it will remove
         * Storage::disk('local')->path($clearName), $this->media->name
         */

        $this->template = [
            'path' => cleanName($request->tenant->uuid . '/' . $this->media->path),
            'name' => cleanName($this->media->name),
            'disk' => 'local',
            'url' => $url,
        ];
        $this->text = (new Pdf())->setPdf(Storage::disk('local')->path($clearName))->text();
        return $this;
    }

    protected function toArray()
    {
        return collect($this->data)->merge([
            'name' => $this->template['name'],
            'path' => $this->template['path'],
            'disk' => 'local',
            'url' => $this->template['url'],
            'row' => $this->row,
        ]);
    }

    protected function ExtractExpresion($row, $obj)
    {
        if (!optional($obj['input'])['inLine']) {
            preg_match_all('/\[[A-Za-z0-9\s]*\]/i', $this->text, $keys);
            $matches = $keys;
        } else {
            preg_match_all('/\[[A-Za-z0-9\s]*\]/i', $this->text, $keys);
            preg_match_all('/.*\[.*\].*/i', $this->text, $matches);
        }

        $data = collect(current($matches))->implode('|~|');
        $pattern = [
            'search' => [],
            'replace' => []
        ];
        collect(current($keys))->unique()->each(function ($match) use (&$pattern, $obj) {
            if (Str::lower($match) === '[!fullname]') {
                /**
                 * @todo check if we have a Snippet or not
                 */
                $pattern['search'][] = $match;
                $pattern['replace'][] = app(Snippet::class)->{Str::lower(Str::replace(['[', ']'], ['', ''], $match))}($this->row, $obj) . ' ';
            } else {
                $pattern['search'][] = $match;
                $pattern['replace'][] = optional($this->row)[Str::lower(Str::replace(['[', ']'], ['', ''], $match))] . ' ' ?? null;
            }
        });

        $rep = explode('|~|', str_replace($pattern['search'], $pattern['replace'], $data));
        collect(current($matches))->unique()->each(function ($ma, $i) use ($rep) {
            if ($rep[$i]) {
                if (Str::contains(trim($ma), "\t")) {
//                    $search = explode("\t", trim($ma));
//                    $replace = explode("\t", trim($rep[$i]));
//                    foreach($search as $k => $v) {
//                        $this->data['search'][] = trim($v);
//                        $this->data['replace'][] = trim($replace[$k]).' ';
//                    }
                    //@todo count chr
                    $search = explode("\t", trim($ma));
                    $replace = explode("\t", trim($rep[$i]));
                    foreach ($search as $k => $v) {
                        $counter = strlen(trim($v)) < strlen($replace[$k]) ?
                            strlen(trim($replace[$k])) - strlen(trim($v)) : 1;
                        $spaces = str_repeat("  ", $counter + 1);
                        $this->data['search'][] = trim($v);
                        if ($k === 0) {
                            $this->data['replace'][] = trim($replace[$k]) . ' ';
                        } else {
                            $this->data['replace'][] = $spaces . trim($replace[$k]) . ' ';
                        }
                    }
                } else {
                    $this->data['search'][] = trim($ma);
                    $this->data['replace'][] = trim($rep[$i]) . ' ';
                }
            }
        });
//        dd($this->data);
        return $this;
    }

}
