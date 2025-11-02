<?php

namespace App\Services;

use Illuminate\Http\Request;
use Spatie\PdfToText\Pdf;

/**
 * Class GetPdfText.
 */
class GetPdfText
{
    public function extractPdfFromRequest(Request $request)
    {
        $pdfs = collect($request->file())->reject(function ($file) {
            if (is_array($file)) {
                return \File::mimeType(...$file) !== 'application/pdf';
            }
            return \File::mimeType($file) !== 'application/pdf';
        })
        ->map(function ($file) {
            if (!is_array($file)) {
                return [$file];
            }
            return $file;
        })->toArray();

        return $this->extractText($pdfs);
    }

    public function extractText($pdfs)
    {
        return collect($pdfs['template'])->map(fn ($pdf) => Pdf::getText($pdf->path()))->toArray();
    }
}
