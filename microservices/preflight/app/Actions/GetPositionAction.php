<?php

namespace App\Actions;

use Lorisleiva\Actions\Concerns\AsAction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Services\PdfApiService;

class GetPositionAction
{
    use AsAction;

    public function handle(Request $request)
    {
        /*
        $path = $request->pdf->path();

         $fileName = $request->pdf->getClientOriginalName();

          $getContent=$request->pdf->getContent();


         $data = Http::withHeaders([
                  'x-api-key' => config('pdfco.key')
              ])->get("https://api.pdf.co/v1/file/upload/get-presigned-url" .
                  "?name=" .$fileName .
                  "&contenttype=application/octet-stream")->object();

              Http::withHeaders([
                  'content-type' => 'application/octet-stream'
              ])->attach(
                  'attachment',
                  $getContent,
                  $path
              )->put($data->presignedUrl);

              $parameters = [
                  "name" => $fileName,
                  "url" => $data->url,

              ];


              $response = Http::withHeaders([
                  'x-api-key' => config('pdfco.key'),
              ])->post("https://api.pdf.co/v1/pdf/convert/to/json", $parameters);


              $content=file_get_contents($response["url"]);

             */
        $pdfAPI=new GetPdfText();

        $getPosition=$pdfAPI->getText($request);


        $pdfAPI=new PdfApiService();
        $pdfAPI->AttachFile($request)->GetPosition();
        $getPosition=$pdfAPI->AttachFile($request)->GetPosition();
        $content=file_get_contents($getPosition->responseUrl);

        //preg_match_all('/"text":\s+{\r\n.*"@fontName":\s+.*\r\n.*"@fontSize":\s+.*\r\n.*"@fontStyle":\s+.*\r\n.*"@color":\s+.*\r\n.*"@x":\s+"(?<x>.*)",\r\n.*"@y":\s+"(?<y>.*)",\r\n.*"@width":\s+.*\r\n.*"@height":\s+.*\r\n.*"#text":\s+"(?<text>\[Signature_Required]\[Signature_Required])"\r\n.*/i', $content, $matches);


        //preg_match_all('/"text":\s+{\r\n.*"@fontName":\s+.*\r\n.*"@fontSize":\s+.*\r\n.*"@fontStyle":\s+.*\r\n.*"@color":\s+.*\r\n.*"@x":\s+"(?<x>.*)",\r\n.*"@y":\s+"(?<y>.*)",\r\n.*"@width":\s+.*\r\n.*"@height":\s+.*\r\n.*"#text":\s+"(?<text>\[.*\]\.*)"\r\n.*/i', $content, $matches);

        preg_match_all('/"@x":\s+"(?<x>.*)",\r\n.*"@y":\s+"(?<y>.*)",\r\n.*"@width":\s+.*\r\n.*"@height":\s+.*\r\n.*"#text":\s+"(?<text>\[.*\]\.*)"\r\n.*/i', $content, $matches);

        $data = collect($matches)
        ->unique()
        ->reject(fn ($v, $rejected) => $rejected === 0)
        ->map(fn ($i) =>
        count(collect($i)->unique()) < 2 ?
            trim(collect($i)->unique()->first()) :
            collect($i)->unique()->map(fn ($r) =>filter_var($r, FILTER_VALIDATE_INT) ? (int) $r : trim($r)))
        ->toArray();


        return $data;
    }
}
