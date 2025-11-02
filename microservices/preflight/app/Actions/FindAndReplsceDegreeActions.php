<?php

namespace App\Actions;

use Lorisleiva\Actions\Concerns\AsAction;
use Illuminate\Http\Request;
use App\Services\PdfApiService;

class FindAndReplsceDegreeActions
{
    use AsAction;

    public function handle(Request $request)
    {
      
        $pdfAPI=new PdfApiService();
        $pdfAPI->AttachDegreeFile($request);
        return $pdfAPI->AttachDegreeFile($request)
            ->FindAndReplaceDegree();
    }
    }
