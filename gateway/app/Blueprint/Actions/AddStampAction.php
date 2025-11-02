<?php

namespace App\Blueprint\Actions;

use App\Blueprint\Actions\Traits\HasReportingTraits;
use App\Blueprint\Contract\BluePrintActionContract;
use App\Services\Preflight\PreflightService;
use Exception;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class AddStampAction implements BluePrintActionContract
{
    use HasReportingTraits;

    public PreflightService $preflightService;

    public function __construct(
        PreflightService $preflightService
    )
    {
        $this->preflightService = $preflightService;
    }

    public function handle(mixed $request, mixed $data, $node = null, mixed $cart = null)
    {
        $pdf = data_get($request->toArray(), $data['input']['from']);

        $pdf['stamps'] = json_encode(data_get($data, "input.stamps"));
        $pdf['url'] = Storage::disk('local')->url($pdf['path'] . '/' . $pdf['name']);
        if (env('APP_ENV') === 'local') {
            cloneData('local', $pdf['path'] . '/' . $pdf['name'], 'carts', $pdf['path'] . '/' . $pdf['name']);
            $pdf['url'] = Storage::disk('carts')->url($pdf['path'] . '/' . $pdf['name']);
        }
        try {
            $file = $this->preflightService->obtainPdfStamp(collect($pdf));
        } catch (Exception $e) {
            throw ValidationException::withMessages(['file' => $e->getMessage()]);
        }
        Storage::disk('local')->delete($pdf['path'] . '/' . $pdf['name']);
        Storage::disk('local')->put($pdf['path'] . '/' . $pdf['name'], $file);
//        $this->createReport('Add Stamp Action', $pdf, $request);
        return $request->merge([
            'AddStampAction' => $pdf
        ]);
    }
}
