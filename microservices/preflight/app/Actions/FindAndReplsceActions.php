<?php

namespace App\Actions;

use Lorisleiva\Actions\Concerns\AsAction;
use Illuminate\Http\Request;
use App\Services\PdfApiService;

class FindAndReplsceActions
{
    use AsAction;

    public function handle(Request $request)
    {
        $pdfAPI=new PdfApiService();
        $pdfAPI->AttachFile($request);
        return $pdfAPI->AttachFile($request)
            ->FindAndReplace();
    }
    }
