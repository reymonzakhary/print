<?php

namespace App\Blueprint\Actions;


use App\Blueprint\Actions\Traits\HasReportingTraits;
use App\Blueprint\Contract\BluePrintActionContract;
use App\Services\PdfCo\PdfCoService;

class FindAndDeleteServiceAction implements BluePrintActionContract
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
            "FindAndDeleteServiceAction" => collect($res)->map(function ($req) use ($request) {
                $send = $req->toArray();
                $result = $this->pdfCoService->findAndDeleteMultipleStrings(
                    search: $req['search'],
                    url: $send['url'],
                );
//                $this->createReport('PDF::CO => Find And Delete Service Action', $send, $request);
                do {
                    $status = $this->pdfCoService->check($result['jobId']); // Possible statuses: "working", "failed", "aborted", "success".
//                    $this->createReport('PDF::CO => Find And Delete Service Action => Check status', $send, $request);
                    if ($status['status'] == "success") {
                        break;
                    } else {
                        if ($status['status'] == "working") {
                            sleep(3);
                        } else {
                            break;
                        }
                    }
                } while (true);
                return $result;
            })
        ]);
    }


}
