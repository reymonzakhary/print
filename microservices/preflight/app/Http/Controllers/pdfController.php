<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use SetaPDF_Core_Document;
use SetaPDF_Core_Document_Page_Annotation_Stamp;
use SetaPDF_Core_Reader_File;
use SetaPDF_Core_Reader_String;
use SetaPDF_Core_Writer_File;

class pdfController extends Controller
{

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function __invoke(Request $request)
    {
        try {

//            $filePath = Storage::disk('public')->path('app/public/1670857947/1235210479.pdf');
            $url = $request->get('url');
            $file = file_get_contents($url);
            $path = time().'/'.rand().'.pdf';
            Storage::disk('local')->put('public/'.$path, $file);
            $filePath = Storage::disk('public')->path($path);
            $reader = new SetaPDF_Core_Reader_String($file);
            if(is_array($request->stamps)) {
                $texts = (object) $request->stamps;
            }else{
                $texts = json_decode($request->stamps);
            }

            $writer = new SetaPDF_Core_Writer_File($filePath);
            $document = SetaPDF_Core_Document::load($reader, $writer);
            $pages = $document->getCatalog()->getPages();
            $pageCount = $pages->count();
            foreach (range(1,$pageCount) as $page){
                $page = $pages->getPage($page);
                $annotations = $page->getAnnotations();
                collect($texts)->map(function ($i, $index) use ($annotations) {
                    // create the text annotation
                    $annotation = new SetaPDF_Core_Document_Page_Annotation_Stamp(array(50, 50, 150, 150));
                    $annotation->setContents($i->text);
                    $annotation->setTextLabel($i->title);
                    $annotation->setPrintFlag(false);
                    $annotations->add($annotation);
                });
            }
            $document->save()->finish();

            $file = new UploadedFile($filePath, $request->name);
            return response()->file($file);
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }
}
