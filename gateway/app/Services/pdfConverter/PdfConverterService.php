<?php


namespace App\Services\pdfConverter;


use App\Contracts\ServiceContract;
use App\Foundation\Media\FileManager;
use App\Http\Requests\Cart\CartStoreRequest;
use App\Models\Tenants\Cart;
use App\Utilities\Traits\ConsumesExternalServices;
use Exception;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;
use Webklex\PDFMerger\Facades\PDFMergerFacade as PDFMerger;

class PdfConverterService extends ServiceContract
{
    use ConsumesExternalServices;

    /**
     * set margin base service uri
     */
    public function __construct()
    {
        $this->base_uri = config('services.replace.base_uri');
        $this->tenant_id = optional(request()->tenant)->uuid;
        $this->tenant_name = optional(request()->hostname)->fqdn;
    }

    /**
     * @param string $tenant
     * @return string
     * @throws GuzzleException
     */
    final public function obtainPdf(CartStoreRequest $request)
    {
        /**
         * original1_3.pdf
         * disk path name
         */
        $cart = Cart::whereUuid(session(tenant()->uuid . '_cart_session'))->first();
        $rand = time();
        $res = $this->sendFile($request, $cart);
        $this->removeCartMedia($cart);
        if (count($res) && !optional($res)['status']) {
            $data = collect($res)->map(function ($va) use ($request, $cart, $rand) {
                return collect($va['data'])->map(function ($i) use ($cart, $rand) {
                    Storage::disk('tenancy')->put(tenant()->uuid . '/converter/' . $cart->id . '/' . $rand . $i['name'], Http::get($i['url'])->getBody());
                    return [
                        'path' => tenant()->uuid . '/converter/' . $cart->id . '/' . $rand . $i['name'],
                        'dirname' => tenant()->uuid . '/converter/' . $cart->id . '/',
                    ];
                });
            });
            $withoutTenant = '/converter/' . $cart->id . '/' . $rand . $request->sku->id . '.pdf';
            $path = $this->merge($data, tenant()->uuid . $withoutTenant);
            $fileDetails = collect(Storage::disk('tenancy')
                ->listContents($path['dirname']))->where('path', $path['path'])->first();
            return $request->merge([
                'converter' => [
                    'disk' => 'tenancy',
                    'name' => $fileDetails['basename'],
                    'path_tenant' => $fileDetails['dirname'],
                    'path' => Str::replace(tenant()->uuid . '/', '', $fileDetails['dirname']),
                    'ext' => $fileDetails['extension'],
                    'size' => $fileDetails['size'],
                    'group' => $fileDetails['extension'],
                    'collection' => $fileDetails['extension'],
                    'type' => Storage::disk('tenancy')->mimeType($fileDetails['path'])
                ]
            ]);
        }
        throw ValidationException::withMessages(optional($res)['message'] ?? 'Service ');

    }

    public function sendFile(CartStoreRequest $request, $cart)
    {
        $path = $request->get('resolution') === 'low' ? 'low' : 'high';
        $template = $this->getMeida($request->sku->product->properties['template']);
        if (!$template) {
            return [
                'message' => [
                    'template' => __('Template not exists')
                ],
                'status' => Response::HTTP_UNPROCESSABLE_ENTITY
            ];
        }
        $xls = $cart->media->whereIn('ext', ['xls', 'xlsx'])->first();
        $pdfs = $cart->media->where('ext', 'pdf');
        $data = collect($pdfs)->map(function ($r) use ($cart) {
            $path = $r['path'] ? tenant()->uuid . $r['path'] . '/' . $r['name'] : tenant()->uuid . '/' . $r['name'];
            return ["pdfs[]", Storage::disk('carts')->get($path), $r['name']];
        })->merge([
            ['xls', Storage::disk('carts')->get(tenant()->uuid . (!$xls['path'] ? '/' : $xls['path']) . $xls['name']), $xls['name']],
        ]);
        $uri = $this->base_uri . '/api/pdf/' . $path;

        $result = collect($request->variations)->map(function ($i) use ($request, $data, $path, $template, $uri) {
            if ($vtemplate = $this->getMeida($i['variation']->properties['template'])) {
                $data = $data->merge([
                    ['template', $vtemplate['file'], $vtemplate['name']]
                ]);
            } else {
                $data = $data->merge([
                    ['template', $template['file'], $template['name']]
                ]);
            }
            $serveicResponce = Http::attach($data->toArray())->post($uri)->json();
            if (optional($serveicResponce))
                return $serveicResponce;
            else {
                throw new Exception(__('Service Down'));
            }
        });
        if (!count($request->variations)) {
            $data = $data->merge([
                ['template', $template['file'], $template['name']]
            ]);
            $res = Http::attach($data->toArray())->post($uri)->json();
            if (optional($res)['status'] && optional($res)['status'] === 404) {
                throw new Exception(__($res['message']));
            }
            $result[] = $res;
        }

        return $result->toArray();

    }

    public function getMeida($template)
    {
        $className = "App\Models\Tenants\\" . optional($template)['mode'];
        if (class_exists($className)) {
            $data = app($className)->find($template['id']);
            if ($media = $data->media->first()) {
                return [
                    'file' => Storage::disk('tenancy')->get(tenant()->uuid . '/' . $media['path'] . '/' . $media['name']),
                    'name' => $media->name
                ];
            }
        }
        return false;
    }

    public function merge($pdfs, $output)
    {
        $pdfMerger = PDFMerger::init();
        $dirname = collect($pdfs)->map(function ($p) use ($pdfMerger, $output) {
            return collect($p)->map(function ($f) use ($pdfMerger, $output) {
                $pdfMerger->addPDF(storage_path('app/tenancy/tenants/' . $f['path']), 'all');
                return [
                    'dirname' => $f['dirname'],
                    'path' => $output
                ];
            });

        });
        $pdfMerger->merge();
        Storage::disk('tenancy')->put($output, $pdfMerger->output());
        collect($pdfs)->map(function ($p) {
            collect($p)->map(function ($v) {
                Storage::disk('tenancy')->delete($v);
            });
        });
        return $dirname->first()->first();
    }

    public function removeCartMedia($cart)
    {
        $media = $cart->media;
        $cart->detachMedia();
        $fileManager = app(FileManager::class);

        collect($media)->map(function ($file) use ($fileManager) {
            Storage::disk('carts')->delete(tenant()->uuid . '/' . $file['path'] . $file['name']);
            (new \App\Models\Tenants\Media\FileManager())->find($file->id)->delete();
        });
    }
}
