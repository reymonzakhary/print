<?php

namespace App\Services;

use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Imports\StudentImport;
use Illuminate\Support\Facades\Http;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;
/**
 * Class PdfApiService.
 */
class PdfApiService
{
    public $data;
    public $urlUpload;
    public $search;
    public $replace;
    public $pdf;
    public $template;
    public $type;
    public $signature;


    public function AttachFile(Request $request)
    {
        $template = $request->file('template');
       
        $this->search = $request->search;
        $this->replace = $request->replace;
        /*$pdf=new Rpdf();
        $pdf->AddPage();
        $pdf->SetFont('Helvetica', '', 2);
        $pdf->SetTextColor(108,145,188);
        $pdf->TextWithRotation(208, 215,'[DiplSuppNr]-[Program ID]-1-[Student Nr]', 90,);
        $pdf->SetFontSize(1);
        $pdf->Output('F',Storage::path('mm.pdf'));
        Artisan::call('add:layer '.Storage::path('mm.pdf').' '.  $template.' '.storage_path('/app') . '/' .   'ss.pdf');
              */

           $dataApi = Http::withHeaders([ 
                'x-api-key' => config('pdfco.key')
            ])->get("https://api.pdf.co/v1/file/upload/get-presigned-url" .
                "?name=" . urlencode($this->template->getClientOriginalName()) .
                "&contenttype=application/octet-stream")->object();

                Http::withHeaders([
                    'content-type' => 'application/octet-stream'
                ])->attach('attachment', $this->template->getContent(), $this->template->getPath())->put($dataApi->presignedUrl);

         $this->urlUpload=$dataApi->url; 
         
           return $this;
       
    }

    public function AttachDegreeFile(Request $request)
    {
    
        $this->pdf=$request->file('pdf');
        $this->template = $request->file('template');
        $this->type =$request->input('type');
        $this->search = $request->search;
        $this->replace = $request->replace;
        $this->signature=$request->file('signature');
        /*$pdf=new Rpdf();
        $pdf->AddPage();
        $pdf->SetFont('Helvetica', '', 2);
        $pdf->SetTextColor(108,145,188);
        $pdf->TextWithRotation(208, 215,'[DiplSuppNr]-[Program ID]-1-[Student Nr]', 90,);
        $pdf->SetFontSize(1);
        $pdf->Output('F',Storage::path('mm.pdf'));
        Artisan::call('add:layer '.Storage::path('mm.pdf').' '.  $template.' '.storage_path('/app') . '/' .   'ss.pdf');
              */

           $dataApi = Http::withHeaders([ 
                'x-api-key' => config('pdfco.key')
            ])->get("https://api.pdf.co/v1/file/upload/get-presigned-url" .
                "?name=" . urlencode($this->pdf->getClientOriginalName()) .
                "&contenttype=application/octet-stream")->object();

                $u=Http::withHeaders([
                    'content-type' => 'application/octet-stream'
                ])->attach('attachment', $this->pdf->getContent(), $this->template->getPath())->put($dataApi->presignedUrl);

                $this->urlUpload=$dataApi->url; 

           return $this;
       
    }
    /*public function GetPosition()
        {
            $parameters = [
                "name" => $this->fileName,
                "url" => $this->url,

            ];

            $response = Http::withHeaders([
                'x-api-key' => config('pdfco.key'),
            ])->post("https://api.pdf.co/v1/pdf/convert/to/json", $parameters);

            $this->responseUrl=$response['url'];

            return $this;
        }*/

    public function FindAndReplace()
    {
             $nameFile=rand(0,9999).'.pdf';

             $response =  Http::withHeaders([
                'x-api-key' => config('pdfco.key'),
            ])->post("https://api.pdf.co/v1/pdf/edit/replace-text", [
                "name"           =>$nameFile,
                "url"            => $this->urlUpload,
                "searchStrings"  => $this->search,
                "replaceStrings" => $this->replace,
            ]);

            return [
                "name" => $nameFile,
                "url" => $response['url']
            ];



    }


    public function FindAndReplaceDegree()
    {
             $nameFile=rand(0,9999).'.pdf';

             $response =  Http::withHeaders([
                'x-api-key' => config('pdfco.key'),
            ])->post("https://api.pdf.co/v1/pdf/edit/replace-text", [
                "name"           =>$nameFile,
                "url"            => $this->urlUpload,
                "searchStrings"  => $this->search,
                "replaceStrings" => $this->replace,
            ]);
          
            Storage::disk('local')->put($nameFile, Http::get($response['url'])->getBody());
            
if ($this->type=='high') {
    $newNameArray1 = explode('.', $nameFile);
    array_splice($newNameArray1, -1, 0, ['-1']);
    $ex1 = array_pop($newNameArray1);
    $newName1 = implode('', $newNameArray1) . '.' . $ex1;
    $newName2 = Str::replace('-1', '-2', $newName1);
    $this->newName3 = Str::replace('-2', '-3', $newName2);
    $this->filenames1 = explode('/', $this->newName3);
    $this->filenames1 = array_pop($this->filenames1);
    $deletedFile = [];
    $cmd = "convert " . Storage::path('signature.pdf') . " -transparent white -page  a4+20+172 -quality 1200 " . storage_path('/app') . '/' . $newName1;
    $deletedFile[] = $newName1;
        
    exec($cmd);
          
    $cmds = "pdftk " . Storage::path($nameFile) . " background " . Storage::path($nameFile) . " output " . storage_path('/app') . '/' . $newName2;
    $deletedFile[] =  $newName2;
    exec($cmds);


    $cmdss = "pdftk A=" . Storage::path($nameFile) . " B=" . Storage::path($newName2) . " cat A1 B2 output " . storage_path('/app') . '/' . $this->newName3;


    exec($cmdss);


    collect($deletedFile)->map(function ($f) {
        Storage::delete($f);
    });

    $data = Http::withHeaders([
        'x-api-key' => config('pdfco.key')
    ])->get("https://api.pdf.co/v1/file/upload/get-presigned-url" .
        "?name=" . urlencode($this->newName3) .
        "&contenttype=application/octet-stream")->object();

    $this->uploadedFileUrl = $data->url;
    $uploadFileUrl = $data->presignedUrl;
   


    $upload = Http::withHeaders([
        'content-type' => 'application/octet-stream'
    ])->attach(
        'attachment', Storage::get($this->newName3), $this->filenames1
    )->put($uploadFileUrl);
    $parameters = [
        "name" => $this->filenames1,
        "url"  => $this->uploadedFileUrl,
        "searchStrings" => $this->search


    ];

    $response = Http::withHeaders([
        'x-api-key' => config('pdfco.key'),
    ])->post("https://api.pdf.co/v1/pdf/edit/delete-text", $parameters);


    return [
        'name' => $nameFile,
        'url' => $response["url"]
    ];

}else{

    
    Storage::disk('local')->putFileAs('template',$this->template,$this->template->getClientOriginalName());

    $newNameArray1 = explode('.', $nameFile);
    array_splice($newNameArray1, -1, 0, ['-low']);
    $ex1 = array_pop($newNameArray1);
    $newName1 = implode('', $newNameArray1) . '.' . $ex1;
    $newName2 = Str::replace('-low', '-low-1', $newName1);
    $newName3 = Str::replace('-low-1', '-low-2', $newName2);
    $newName4 = Str::replace('-low-2', '-low-4', $newName3);
    $newName5 = Str::replace('-low-4', '-low-5', $newName4);
    $newName6 = Str::replace('-low-5', '-low-6', $newName5);
    $this->newName7 = Str::replace('-low-6', '-low-7', $newName6);

    $this->filenames1 = explode('/',  $this->newName7);
    $this->filenames1 = array_pop($this->filenames1);
    $deletedFile = [];


    Artisan::call('add:layer '. Storage::path('template/'.$this->template->getClientOriginalName()).' '. Storage::path($nameFile).' '.storage_path('/app') . '/' .   $newName1);
    //$cmd = "pdftk " . Storage::path('template/'.$this->template->getClientOriginalName()) . " multibackground " . Storage::path($nameFile) . " output " . storage_path('/app') . '/' .   $newName1 ;

    //exec($cmd);

    $pdf=new Rpdf();
    $n = 2;


    for ($x = 1; $x <= $n; $x++) {


        $pdf->AddPage();
        $pdf->SetFont('Helvetica', '', 5.18);
        $pdf->SetTextColor(108,145,188);
        $pdf->TextWithRotation(205, 287, 1-1-1, 90,);
        $pdf->SetFontSize(5.18);

    }
    
    $pdf->Output('F',Storage::path($newName2));
  
        Artisan::call('add:layer '.Storage::path($newName2).' '. Storage::path($newName1).' '.storage_path('/app') . '/' .   $newName3);
        Artisan::call('converter '.Storage::path('signature.pdf').' '. storage_path('/app') . '/' . $newName4);

        Artisan::call('add:layer '.Storage::path($newName1).' '. Storage::path($newName4).' '.storage_path('/app') . '/' .   $newName5);


        Artisan::call('add:layer '.Storage::path($newName2).' '. Storage::path($newName5).' '.storage_path('/app') . '/' .   $newName6);

        Artisan::call('marge '.Storage::path($newName3).' '. Storage::path($newName6).' '.storage_path('/app'). '/' . $this->newName7);


        $data = Http::withHeaders([
            'x-api-key' => config('pdfco.key')
        ])->get("https://api.pdf.co/v1/file/upload/get-presigned-url" .
            "?name=" . urlencode($this->newName7) .
            "&contenttype=application/octet-stream")->object();


        // Get URL to use for the file upload
        $uploadFileUrl = $data->presignedUrl;
        // Get URL of uploded file to use with later API calls
        $this->uploadedFileUrl = $data->url;



        $upload = Http::withHeaders([
            'content-type' => 'application/octet-stream'
        ])->attach(
            'attachment', Storage::get($this->newName7), $this->filenames1
        )->put($uploadFileUrl);

        $parameters = [
            "name" => $this->filenames1,
            "url"  => $this->uploadedFileUrl,
            "searchStrings" => $this->search


        ];

        $response = Http::withHeaders([
            'x-api-key' => config('pdfco.key'),
        ])->post("https://api.pdf.co/v1/pdf/edit/delete-text", $parameters);

        return [
            'name' => $nameFile,
            'url' => $response["url"],
        ];
}


    
         


    }




    public function FindAndDELETE(array $searchResult)
    {
        $parameters = [
            "name" => $this->fileName,
            "url"  => $this->url,
            "searchStrings" => $this->search

        ];

        $response = Http::withHeaders([
            'x-api-key' => config('pdfco.key'),
        ])->post("https://api.pdf.co/v1/pdf/edit/delete-text", $parameters);


        return [
            'name' => $this->filename,
            'url' => $response["url"]
        ];
    }
}
