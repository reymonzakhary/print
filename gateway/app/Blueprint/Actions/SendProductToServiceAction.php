<?php

namespace App\Blueprint\Actions;


use App\Blueprint\Actions\Traits\HasReportingTraits;
use App\Blueprint\Contract\BluePrintActionContract;
use App\Services\PdfCo\PdfCoService;
use Illuminate\Support\Facades\Storage;

class SendProductToServiceAction implements BluePrintActionContract
{
    use HasReportingTraits;

    public PdfCoService $pdfCoService;

    public function __construct(
        PdfCoService $pdfCoService
    )
    {
        $this->pdfCoService = $pdfCoService;
    }

    public function handle($request, $data, $node = null, mixed $cart = null)
    {
        $res = data_get($request->toArray(), $data['input']['from']);

        $request->merge([
            "SendProductToServiceAction" => collect($res)->map(function ($req) use ($request) {
                $send = $req->toArray();

                if (env('APP_ENV') === 'local') {
                    cloneData(
                        $req['disk'], $req['path'] . '/' . $req['name'],
                        'carts', $req['path'] . '/' . $req['name']
                    );
                    $send['url'] = Storage::disk('carts')->url($req['path'] . '/' . $req['name']);
                }

                $result = $this->pdfCoService->findAndReplaceMultipleStrings(
                    search: $req['search'],
                    replace: $req['replace'],
                    url: $send['url'],
                    sync: false
                );

//
//                $this->createReport('PDF.CO => find And Replace Multiple Strings', $result, $request);

//                do {
//                    if(!optional($result)['jobId']){
//                        throw ValidationException::withMessages([
//                            'jobId' => _('we cant handle this request ! => Send Product To Service')
//                        ]);
//                        break;
//                    }
//                    $status = $this->pdfCoService->check(optional($result)['jobId']); // Possible statuses: "working", "failed", "aborted", "success".
////                    $this->createReport('PDF.CO => find And Replace Multiple Strings => Check Status', $status, $request);
//                    if ($status['status'] == "success") {
//                        break;
//                    } else {
//                        if ($status['status'] == "working") {
//                            sleep(3);
//                        } else {
//                            break;
//                        }
//                    }
//                } while (true);
                return $result;
            })
        ]);
    }


}
