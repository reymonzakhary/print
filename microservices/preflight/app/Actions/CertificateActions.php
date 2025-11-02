<?php

namespace App\Actions;

use Lorisleiva\Actions\Concerns\AsAction;
use App\Services\PdfApiNewService;
use Illuminate\Http\Request;

class CertificateActions
{
    use AsAction;

    public function handle(Request $request)
    {
        $pdfAPI=new PdfApiNewService();

        $template    = $request->file('template');
        $search      = $request->search;
        $replace     = $request->replace;
        $fileName    = $template->getClientOriginalName();
        $fileContent = $template->getContent();
        $filePath    = $template->getPath();
        $urlUpload   = $pdfAPI->AttachFile($fileName, $fileContent, $filePath);
        $nameFile=rand(0, 9999).'.pdf';
        $parameters =
            [
                "name"           => $nameFile,
                "url"            => $urlUpload,
                "searchStrings"  => $search,
                "replaceStrings" => $replace,
            ];

        return $pdfAPI->FindAndReplace($parameters);
    }
}
