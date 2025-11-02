<?php

namespace App\Actions\Pdf;

use Illuminate\Support\Facades\Storage;
use Webklex\PDFMerger\Facades\PDFMergerFacade as PDFMerger;

class MergePdfFilesActions
{
    public function handle(array $pdfs, string $output, $prefix = 'app/tenancy/tenants/')
    {
        $pdfMerger = PDFMerger::init();
        $dirname = collect($pdfs)->map(function ($p) use ($pdfMerger, $output, $prefix) {
            $pdfMerger->addPDF(storage_path($prefix . $p['path']), 'all');
            return [
                'dirname' => $p['dirname'],
                'path' => $output
            ];
        });
        $pdfMerger->merge();
        Storage::disk('tenancy')->put($output, $pdfMerger->output());
        collect($pdfs)->map(function ($p) {
            Storage::disk('tenancy')->delete($p);
        });
        return $dirname->first();
    }
}
