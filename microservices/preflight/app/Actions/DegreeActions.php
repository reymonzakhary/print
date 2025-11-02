<?php

namespace App\Actions;

use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Services\PdfApiNewService;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;
use Lorisleiva\Actions\Concerns\AsAction;
use App\Services\Rpdf;
use Spatie\PdfToText\Pdf;

class DegreeActions
{
    use AsAction;

    public function handle(Request $request)
    {


        $pdfAPI = new PdfApiNewService();
        $pdf = $request->file('pdf');
        $template = $request->file('template');
        $type = 1;

        //  $signature   = $request->file('signature');
        $fileName = $pdf->getClientOriginalName();
        $fileContent = $pdf->getContent();
        $filePath = $pdf->getRealPath();


        $urlUpload = $pdfAPI->AttachFile($fileName, $fileContent, $filePath);
        $nameFile = rand(0, 9999) . '.pdf';
        $search = [];
        $replace = [];
        $student = '';
        $program = '';
        $page = [];
        $myDocument = '';
        if ($request->get('data')) {
            collect(json_decode($request->get('data')))->map(function ($v) use (&$search, &$replace, $pdf, &$student, &$program, &$page, &$myDocument) {
                $student = $v->student;
                $program = $v->program;
                $page[] = $v->page;

                return collect($v->selector)->map(function ($i) use (&$search, &$replace, $pdf, $v, &$student, &$program, &$page, &$myDocument) {

                    if ($i->type === 'regx') {
                        $file = Pdf::getText($pdf->path());
                        preg_match_all($i->key, $file, $keys);
                        foreach ($keys[$i->select] as $v) {
                            if(in_array($v, $search)) continue;
                                $search[] = $v;
                                $replace[] = $i->value;
                        }


                    } else {
                        if (!in_array($i->key, $search)) {
                            $search[] = $i->key;
                            $replace[] = $i->value;
                        }

                    }


                });
            });
        }


        $parameters =
            [
                "name" => $fileName,
                "url" => $urlUpload,
                "searchStrings" => $search,
                "replaceStrings" => $replace,

            ];
        $k = array_search('mydocument_name', $search); //$k = 1;
        $response = $pdfAPI->FindAndReplace($parameters);

        Storage::disk('local')->put($nameFile, Http::get($response['url'])->getBody());
        Storage::disk('local')->put($template->getClientOriginalName(), $template->getContent());
        Storage::disk('local')->put($template->getClientOriginalName(), $template->getContent());
        //Storage::disk('local')->put($signature->getClientOriginalName(), $signature->getContent());
        if ($type == 0) {
            $newNameArray1 = explode('.', $nameFile);
            array_splice($newNameArray1, -1, 0, ['-1']);
            $ex1 = array_pop($newNameArray1);
            $newName1 = implode('', $newNameArray1) . '.' . $ex1;
            $newName2 = Str::replace('-1', '-2', $newName1);
            $newName3 = Str::replace('-2', '-3', $newName2);
            $filenames1 = explode('/', $newName3);
            $filenames1 = array_pop($filenames1);
            $deletedFile = [];

            //Artisan::call('converter '.Storage::path($signature->getClientOriginalName()).' '. storage_path('/app') . '/' . $newName1);

            //Artisan::call('add:layer '.Storage::path($nameFile).' '. Storage::path($newName1).' '.storage_path('/app') . '/' .   $newName2);


            //Artisan::call('marge '.Storage::path($nameFile).' '. Storage::path($newName2).' '.storage_path('/app'). '/' . $newName3);

            $deletedFile[] = $newName1;
            $deletedFile[] = $newName2;

            collect($deletedFile)->map(function ($f) {
                Storage::delete($f);
            });
            /*$urlUpload = $pdfAPI->AttachFile($nameFile, file_get_contents(Storage::path($nameFile)), Storage::path($nameFile));
            $nameFile = rand(0, 9999) . '.pdf';
            $parameters =
                [
                    "name" => $fileName,
                    "url" => $urlUpload,
                    "searchStrings" => $search,
                    "replaceStrings" => $replace,
                ];*/
            return $response = $pdfAPI->FindAndReplace($parameters);
        } else {
            $newNameArray1 = explode('.', $nameFile);
            array_splice($newNameArray1, -1, 0, ['-low']);
            $ex1 = array_pop($newNameArray1);
            $newName1 = implode('', $newNameArray1) . '.' . $ex1;
            $newName2 = Str::replace('-low', '-low-1', $newName1);
            $newName3 = Str::replace('-low-1', '-low-2', $newName2);
            $newName4 = Str::replace('-low-2', '-low-4', $newName3);
            $newName5 = Str::replace('-low-4', '-low-5', $newName4);
            $newName6 = Str::replace('-low-5', '-low-6', $newName5);
            $newName7 = Str::replace('-low-6', '-low-7', $newName6);

            $filenames1 = explode('/', $newName7);
            $filenames1 = array_pop($filenames1);
            $deletedFile = [];


            Artisan::call('add:layer ' . Storage::path($template->getClientOriginalName()) . ' ' . Storage::path($nameFile) . ' ' . storage_path('/app') . '/' . $newName1);


            $pdf = new Rpdf();


            for ($x = 0; $x < count($page); $x++) {
                $pdf->AddPage();
                $pdf->SetFont('Helvetica', '', 5.18);
                $pdf->SetTextColor(108, 145, 188);
                $pdf->TextWithRotation(205, 287, $replace[$k] . '-' . $program . '-' . $page[$x] . '-' . $student, 90,);
                $pdf->SetFontSize(5.18);

            }
            $pdf->Output('F', Storage::path($newName2));

            Artisan::call('add:layer ' . Storage::path($newName2) . ' ' . Storage::path($newName1) . ' ' . storage_path('/app') . '/' . $newName3);
//                Artisan::call('converter '.Storage::path($signature->getClientOriginalName()).' '. storage_path('/app') . '/' . $newName4);

            // Artisan::call('add:layer ' . Storage::path($newName1) . ' ' . Storage::path($newName4) . ' ' . storage_path('/app') . '/' . $newName5);


            //Artisan::call('add:layer ' . Storage::path($newName2) . ' ' . Storage::path($newName5) . ' ' . storage_path('/app') . '/' . $newName6);

            //  Artisan::call('marge ' . Storage::path($newName3) . ' ' . Storage::path($newName6) . ' ' . storage_path('/app') . '/' . $newName7);

            //$deletedFile[] = $newName1;
            //$deletedFile[] = $newName2;
            //$deletedFile[] = $newName3;
            //$deletedFile[] = $newName4;
            //$deletedFile[] = $newName5;
            //$deletedFile[] = $newName6;
            collect($deletedFile)->map(function ($f) {
                Storage::delete($f);
            });
            $urlUpload = $pdfAPI->AttachFile($newName3, file_get_contents(Storage::path($newName3)), Storage::path($newName3));
            $nameFile = rand(0, 9999) . '.pdf';
            $parameters =
                [
                    "name" => $fileName,
                    "url" => $urlUpload,
                    "searchStrings" => $search,
                    "replaceStrings" => $replace,
                ];
            return $response = $pdfAPI->FindAndReplace($parameters);
        }
    }
}
