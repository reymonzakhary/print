<?php

namespace App\Http\Controllers\Tenant\Mgr\Custom\Products\Imports;

use App\Actions\Import\Miele\XmlImport;
use App\Http\Controllers\Controller;
use App\Http\Requests\Imports\ImportRequest;
use Illuminate\Http\Response;

class ImportController extends Controller
{


    public function import(ImportRequest $request)
    {
        if ($request->type === "text/xml") {
            $countOFImpotedProduct = (new XmlImport)->storeCategory($request->file('file'));
        }
        /**
         * congratulations response
         */
        return response()->json([
            'message' => __($countOFImpotedProduct . ' products imported with success.'),
            'status' => Response::HTTP_OK
        ], Response::HTTP_OK);
    }


}
