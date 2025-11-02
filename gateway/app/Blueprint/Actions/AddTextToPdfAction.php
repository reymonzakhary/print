<?php

namespace App\Blueprint\Actions;

use App\Blueprint\Actions\Traits\TrashCollectionTraits;
use App\Blueprint\Contract\BluePrintActionContract;
use App\Blueprint\Fpdf\Rotate;
use App\Blueprint\SnippetsCode\Snippet;
use Exception;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AddTextToPdfAction implements BluePrintActionContract
{
    use TrashCollectionTraits;

//        ,HasReportingTraits;

    public function handle(mixed $request, mixed $data, $node = null, mixed $cart = null)
    {


        try {
            $xls = $request[$data['input']['from']['xls']];
            $pdfs = $request[$data['input']['from']['pdf']];
        } catch (Exception $e) {
            $request->merge([
                'AddTextToPdfAction' => collect([])
            ]);
            return;
        }
        $res = $pdfs->map(function ($row, $key) use ($data, $xls, $request) {
            $rep = collect($xls)->first(fn($r) => trim((string)$r['student nr']) === trim((string)$key));
            $pdf = new Rotate();
            $pageCount = $pdf->setSourceFile(cleanName(Storage::disk($row['disk'])->path($row['path'] . DIRECTORY_SEPARATOR . $row['name'])));
            $page_start_at = optional(optional($data['input']['string'])['pages'])['start_at'] ?? 1;

            for ($i = 1; $i <= $pageCount; $i++) {
                $pdf->AddPage('p', 'A4');
                $temp = $pdf->importPage($i);
                $size = $pdf->getTemplateSize($temp);
                $pdf->useImportedPage($temp, 0, 0, $size['width'], $size['height'], true);
                $pdf->SetFont('Helvetica', '', 5);
                $pdf->SetTextColor(108, 145, 188);
                $pdf->rotatedText(
                    $data['input']['string']['x'],
                    $data['input']['string']['y'],
                    $this->getValueExpresion($data['input']['string']['text'], $rep),
                    optional($data['input']['string'])['angle'] ?? 0
                );
                $pdf->SetFontSize(1);
            }
            $time = time() . rand(000, 999);
            $fileName = 'chd-' . $time . $row['name'];
            //$this->addToTrash($request, 'local', $row['path'], $fileName);
            $newPath = Storage::disk($row['disk'])->path(cleanName($row['path'] . DIRECTORY_SEPARATOR . $fileName));
            Storage::disk('local')->makeDirectory(cleanName($row['path']));
            $pdf->Output('F', $newPath);

            $row['name'] = $fileName;
            $row['url'] = asset('local/' . cleanName($row['path'] . DIRECTORY_SEPARATOR . $fileName));
//            $this->createReport('Add Stamp Action', $row->toArray(), $request);
            return $row;
        });
        $request->merge([
            'AddTextToPdfAction' => $res
        ]);
    }

    protected function getValueExpresion($text, $row)
    {
        $newText = $text;
        preg_match_all('/\[[A-Za-z0-9_\-\s]*\]/i', $text, $keys);

        collect(current($keys))->unique()->each(function ($match) use (&$pattern, $row, &$newText) {
            if (Str::lower($match) === '[fullname]') {
                $r = app(Snippet::class)->{Str::lower(Str::replace(['[', ']'], ['', ''], $match))}($row);
                $newText = Str::replace($match, $r, $newText);
            } else {
                $r = $row[Str::lower(Str::replace(['[', ']'], ['', ''], $match))] ?? null;
                $newText = Str::replace($match, $r, $newText);
            }
        })->toArray();
        return $newText;
    }
}
