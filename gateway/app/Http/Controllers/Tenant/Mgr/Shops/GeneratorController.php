<?php

namespace App\Http\Controllers\Tenant\Mgr\Shops;

use App\Blueprint\BluePrintList;
use App\Blueprint\BluePrintNode;
use App\Http\Controllers\Controller;
use App\Http\Requests\Shops\GeneratorRequest;
use App\Models\Tenants\Sku;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class GeneratorController extends Controller
{
    public function show(Sku $sku, Request $request)
    {
        return $sku->product;
    }

    public function generate(Sku $sku, GeneratorRequest $request)
    {
        $blue = $this->bluePrint2($request->validated(), $sku);
        return response()->json([
            'converter' => $blue['converter'],
            'quantity' => $blue['quantity'],
            'product' => $sku->product
        ]);

    }


    public function bluePrint($data)
    {
        $bluePrintList = new BluePrintList('start');
        $bluePrintList->setAction('Approval', ['upload_file', 'pdf_converter']);
        $bluePrintList->add(new BluePrintNode('upload_file', 'custom', [
            'xls' => '',
            'pdfs' => ''
        ]));

        $bluePrintList->add(new BluePrintNode('pdf_converter', 'custom', [
            'xls' => '',
            'pdfs' => ''
        ]));
        $bluePrintList->add(['send_email' => [
            new BluePrintNode('send_by_email'),
            new BluePrintNode('send_by_ftp')
        ]]);
        return $bluePrintList->next(request());

    }


    public function bluePrint2($data, $sku)
    {
        $bluePrintList = new BluePrintNode('start');

        $bluePrintList->add((new BluePrintNode('upload_file', 'action', [
            'xls' => '',
            'pdfs' => '',
            'template' => ''
        ]))->setConfig([
            'Approval' => true
        ])->setActions('Approval', [
            new BluePrintNode('pdf_converter')
        ], []));

//        $bluePrintList->add(new BluePrintNode('pdf_converter'));
        $responce = $bluePrintList->next(request());
        Session::put(tenant()->uuid . 'shop', [
            $sku->id => $responce['converter']
        ]);
        return $responce;
    }
}
