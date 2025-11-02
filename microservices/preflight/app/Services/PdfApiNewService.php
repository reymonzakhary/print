<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;


/**
 * Class PdfApiNewService.
 */
class PdfApiNewService
{
    public function AttachFile($fileName, $fileContent, $filePath)
    {
        $dataApi = Http::withHeaders([
            'x-api-key' => config('pdfco.key')
        ])->get("https://api.pdf.co/v1/file/upload/get-presigned-url" .
            "?name=" . urlencode($fileName) .
            "&contenttype=application/octet-stream")->object();
        $update=Http::withHeaders([
            'content-type' => 'application/octet-stream'
        ])->attach('attachment', $fileContent, $filePath)->put($dataApi->presignedUrl);


        $urlUpload=$dataApi->url;

        return $urlUpload;
    }

    public function FindAndReplace($parameters)
    {
        try {
            $response =  Http::withHeaders([
                'x-api-key' => config('pdfco.key'),
            ])->post("https://api.pdf.co/v1/pdf/edit/replace-text", $parameters);

        } catch (\Exception $e) {
            dd($e->getMessage());
        }



            return [
                "name" => $parameters['name'],
                "url"  => $response['url']
            ];
    }

    public function FindAndDelete($parameters)
    {

             $response =  Http::withHeaders([
                'x-api-key' => config('pdfco.key'),
            ])->post("https://api.pdf.co/v1/pdf/edit/replace-text", $parameters);

            return [
                "name" => $parameters['name'],
                "url"  => $response['url']
            ];
    }
}
