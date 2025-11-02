<?php

namespace App\Actions;

use Lorisleiva\Actions\Concerns\AsAction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

class AddLayersActions
{
    use AsAction;

    public function handle(Request $request)
    {
        $pdfData=FindAndReplsceActions::run($request);
   
       Artisan::call('add:layer ' .asset('template.pdf').' '.asset('diploma.pdf').' '.public_path('ress.pdf'));
       
    }
}