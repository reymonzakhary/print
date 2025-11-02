<?php

namespace App\Actions;

use Lorisleiva\Actions\Concerns\AsAction;

use Illuminate\Http\Request;

use Spatie\PdfToText\Pdf;

use App\Services\GetPdfText;


class GetPdfDataAction
{
    use AsAction;
   

    public function handle(Request $request)
    {
       
        /*$pdfDataArray=[];
        $pdfAPI=new GetPdfText();

        $getPdfText=$pdfAPI->extractPdfFromRequest($request);
          
        
        foreach($getPdfText as $getPdfTexts){

    preg_match_all('/\[Student.?Nr:(?<student>[\s?\d]+)]\[Program:(?<program>[\s?\d]+)]\[Language:(?<language>\s?[A-Za-z]{2})]\[Page:(?<page>\s?\d+)]|Naam\s+\/\s+Name\s*:\s*(?<name>(.*))|Geboortedatum\s+\/\s+Date\s*of\s*birth\s*:\s*(?<birthday>(.*))|Geboorteplaats\s+\/\s+Place\s*of\s*birth\s*:\s*(?<place>(.*))/i', $getPdfTexts, $matches);

    $pdfData = collect($matches)
    ->unique()
    ->reject(fn ($v, $rejected) => $rejected === 0)
    ->map(
        function ($i) {
            $co = collect($i)->filter();
            $t  = array_merge([], $co->toArray());
            return collect($t)->unique()->collect($t)->unique()->map(fn ($r) =>filter_var($r, FILTER_VALIDATE_INT) ? (int) $r : trim($r));
        }
    )->toArray();

     array_push($pdfDataArray,$pdfData);
     }
    return $pdfDataArray;*/

            $pdfDataArray=[];
            $pdfAPI=new GetPdfText();
    
            $getPdfText=$pdfAPI->extractPdfFromRequest($request);
              
            
            foreach($getPdfText as $getPdfTexts){
    
        preg_match_all('/\[.*\]/i', $getPdfTexts, $matches);
    
        //preg_match_all('/\[Student.?Nr:(?<student>[\s?\d]+)]\[Program:(?<program>[\s?\d]+)]\[Language:(?<language>\s?[A-Za-z]{2})]\[Page:(?<page>\s?\d+)]|Naam\s+\/\s+Name\s*:\s*(?<name>(.*))|Geboortedatum\s+\/\s+Date\s*of\s*birth\s*:\s*(?<birthday>(.*))|Geboorteplaats\s+\/\s+Place\s*of\s*birth\s*:\s*(?<place>(.*))/i', $getPdfTexts, $matches);
    
        $pdfData = collect($matches)
        ->unique()
        ->map(
            function ($i) {
                return array_merge([], collect($i)->filter()->unique()->map(fn ($r) =>filter_var($r, FILTER_VALIDATE_INT) ? (int) $r : trim($r))->toArray());
            }
        )->toArray();
    
         
         }
        return $pdfData;
    
            
        }
    }
    
        
    

