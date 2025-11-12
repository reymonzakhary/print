<?php

declare(strict_types=1);

namespace App\Http\Controllers\System\V2\Mgr\Companies;

use App\DTO\Tenant\Orders\ItemDTO;
use App\Events\Quotations\SupplierQuotationEvent;
use App\Foundation\Status\Status;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Tenant\Mgr\Quotations\Items\Media\MediaController;
use App\Http\Requests\Items\Media\MediaStoreRequest;
use App\Http\Requests\System\Companies\StoreQuotationRequest;
use App\Http\Resources\Suppliers\QuotationSupplierResource;
use App\Models\Contract;
use App\Models\Quotation;
use App\Models\Tenant\Quotation as TenantQuotation;
use App\Utilities\Traits\ConsumesExternalServices;
use File;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Response;

class QuotationController extends Controller
{
    use ConsumesExternalServices;

    public $base_uri;

    /**
     * @param $company
     * @param $quotation
     * @return JsonResponse|AnonymousResourceCollection
     */
    public function index(
        $company,
        $quotation
    ): AnonymousResourceCollection|JsonResponse
    {
        if (auth()->user()->companies()->where('company_user.company_id', $company)->exists()) {
            $quotations = [];
            $suppliers = Quotation::where([
                'company_id' => auth()->user()?->company?->id,
                'external_id' => $quotation,
            ])->with('supplier.website')->get();

            collect($suppliers)->each(function ($supplierQuotation) use (&$quotations, $quotation) {
                switchSupplier($supplierQuotation->supplier->website->uuid);
                $tenantQuotation = TenantQuotation::where([
                    ['id', $supplierQuotation->internal_id],
                    ['user_id', auth()->user()->id],
                    ['connection', 'cec'],
                ])->with([
                    'orderedBy' => function ($qs) {
                        return $qs->select(
                            'id',
                            'email'
                        );
                    },
                    'orderedBy.profile',
                    'lockedBy' => function ($qs) {
                        return $qs->select(
                            'id',
                            'email'
                        );
                    },
                    'context' => function ($qs) {
                        return $qs->select(
                            'id',
                            'name'
                        );
                    },

                    'delivery_address',
                    'invoice_address',
                    'services',
                    'items',
                    'items.media',
                    'items.media.tags',
                    'items.services',
                    'items.addresses',
                    'items.children',
                    'items.children.addresses',
                ])->first();

                if ($tenantQuotation) {

                    $contract = Contract::where('requester_id', auth()->user()->company->id)
                        ->where('receiver_id', $supplierQuotation->supplier->id)
                        ->first();

                    $tenantQuotation->contract = $contract;
                    $tenantQuotation->external_id = (int)$quotation;
                    $quotations[] = $tenantQuotation;
                }
            });

            return QuotationSupplierResource::collection($quotations)
                ->additional([
                    'status' => Response::HTTP_OK,
                ]);
        }

        /**
         * Error response if company id not found
         */
        return response()->json([
            "message" => __("Company not found!"),
            "status" => Response::HTTP_NOT_FOUND
        ], Response::HTTP_NOT_FOUND);

    }

    /**
     * @param StoreQuotationRequest $request
     * @return AnonymousResourceCollection
     */
    public function store(
        StoreQuotationRequest $request
    ): AnonymousResourceCollection
    {

        $contracts = Contract::with('supplier.website')
            ->where([
                ['requester_id', auth()->user()?->company?->id],
                ['active', true]
            ])
            ->whereHas('supplier', function ($q) use ($request) {
                return $q->whereIn('hostnames.id', $request->validated('suppliers'));
            })->get();
        $quotations = [];
        $contracts->each(function ($contract) use ($request, &$quotations) {
            // switch db connection to tenant database
            switchSupplier($contract->supplier->website->uuid);

            if (
                !Quotation::where([
                    ['company_id', auth()->user()->company->id],
                    ['hostname_id', $contract->hostname_id],
                    ['external_id', $request->validated()['quotation_id']]
                ])->exists()
            ) {

                // create quotation in the tenant database
                $tenantQuotation = TenantQuotation::create(array_merge($request->validated(),
                    [
                        'ctx_id' => 1
                    ]));
//                 attach items to the tenant quotation

                foreach ($request->validated('items') as $key => $item) { // setting items status to watiting
                    $item['st'] = Status::NEW;
                    $item['supplier_id'] = $contract->supplier->website->uuid;
                    $item['supplier_name'] = $contract->supplier->fqdn;
                    $req =  new MediaStoreRequest();
                    $dir = $contract->supplier->website->uuid ."/files/". Str::random(10) . '/';
                    if (array_key_exists('files', $item)) {
                        $disk = 'local';
                        Storage::disk($disk)->makeDirectory($dir);
                        collect($item['files'])->each(function ($v, $k) use ($disk, $dir, $req, &$files) {
                            $ext = Str::afterLast($v['url'], '.')??'pdf';
                            $file = "$dir{$v['name']}.{$ext}";
                            Storage::disk($disk)->put($file, Http::withOptions(['verify' => false])->get($v['url'])->body());
                            $req->files->set($v['name'], new UploadedFile(
                                Storage::disk('local')->path($file),
                                $v['name'] . ".$ext",
                                \File::mimeType(Storage::disk('local')->path($file))
                            ));
                        });
                    }


                    $item = $tenantQuotation->items()->create(ItemDTO::fromExternal($item));

                    app(MediaController::class)
                        ->store(
                            $tenantQuotation,
                            $item,
                            $req
                        );

                    if ($req->files && $dir) {
                        Storage::disk('local')->deleteDirectory($dir);
                    }

                    // if exists
                    // download

                    // store

                    // remove

                }

                // load quotation with it's related data
                $tenantQuotation = TenantQuotation::with(['address', 'invoice_address', 'items'])
                    ->where('id', $tenantQuotation->id)->first();
                $grossPriceSum = $tenantQuotation->items()
                    ->selectRaw('SUM((product->\'price\'->>\'gross_price\')::numeric) as total')
                    ->value('total');
                $tenantQuotation->update([
                    'price' => $grossPriceSum
                ]);

                // attach data to quotation response
                $tenantQuotation->contract = $contract;
                $tenantQuotation->external_id = $request->validated()['quotation_id'];

                $quotations[] = $tenantQuotation;

                // save the sent quotation in cec database
                $quotation = Quotation::create([
                    'external_id' => $request->validated()['quotation_id'],
                    'hostname_id' => $contract->supplier->id,
                    'internal_id' => $tenantQuotation->id,
                    'company_id' => auth()->user()->company->id,
                    'st' => Status::NEW,
                    'contract_id' => $contract->id,
                ]);

                event(new SupplierQuotationEvent($contract, $quotation));

            }
        });


        return QuotationSupplierResource::collection($quotations);
    }

    /**
     * @param $company
     * @param $contract
     * @param $quotation
     * @return QuotationSupplierResource|JsonResponse
     */
    public function accept(
        $company,
        $contract,
        $quotation
    ): JsonResponse|QuotationSupplierResource
    {
        if (auth()->user()->companies()->where('company_user.company_id', $company)->exists()) {
            $contract = Contract::where([
                ['id', $contract],
                ['requester_id', $company]
            ])->with('supplier.website')->first();

            if (!$contract) {
                return response()->json([
                    'message' => __('Contract not found!.'),
                    'status' => Response::HTTP_NOT_FOUND
                ], Response::HTTP_NOT_FOUND);
            }

            if ($contract->st === Status::PENDING) {
                return response()->json([
                    'message' => __('Producer is already working on this requested quotation, can not be accepted yet.'),
                    'status' => Response::HTTP_UNPROCESSABLE_ENTITY
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            if ($contract->st === Status::REJECTED) {
                return response()->json([
                    'message' => __('This quotation has been rejected, by producer'),
                    'status' => Response::HTTP_UNPROCESSABLE_ENTITY
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            $sentQuotation = Quotation::where([
                'external_id' => $quotation,
                'hostname_id' => $contract->receiver_id,
                'company_id' => $company,
            ])->with('supplier.website')->first();

            if (!$sentQuotation) {
                return response()->json([
                    'message' => __('Quotation not found!.'),
                    'status' => Response::HTTP_NOT_FOUND
                ], Response::HTTP_NOT_FOUND);
            }

            // accept the quotation
            switchSupplier($sentQuotation->supplier->website->uuid);

            $quotation = TenantQuotation::where('id', $sentQuotation->internal_id)->first();

            if (!$quotation || $quotation->st === Status::NEW) {
                return response()->json([
                    'message' => __('Waiting for supplier\'s response.'),
                    'status' => Response::HTTP_UNPROCESSABLE_ENTITY
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            $quotation->update([
                'st' => Status::ACCEPTED
            ]);

            $quotation->items()->update(['st' => Status::ACCEPTED]);

            $this->declineOthers($sentQuotation);
            switchSupplier($sentQuotation->supplier->website->uuid);
            $quotation = TenantQuotation::with('address', 'invoice_address', 'items')->find($quotation->id);
            $quotation->contract = $contract;
            return QuotationSupplierResource::make($quotation)
                ->additional([
                    'message' => __('Quotation accepted.'),
                    'status' => Response::HTTP_OK
                ]);

        }

        /**
         * Error response if company id not found
         */
        return response()->json([
            "message" => __("Company not found!"),
            "status" => Response::HTTP_NOT_FOUND
        ], Response::HTTP_NOT_FOUND);

    }

    /**
     * @param Quotation $sentQuotation
     */
    public function declineOthers(
        Quotation $sentQuotation
    ): void
    {
        $otherQuotations = Quotation::where([
            ['external_id', $sentQuotation->external_id],
            ['id', '!=', $sentQuotation->id],
            ['company_id', $sentQuotation->company_id],
        ])->with('supplier.website');

        $otherQuotations->update(['st' => Status::CANCELED]);

        $otherQuotations->get()->each(function (Quotation $quotation) {
            switchSupplier($quotation->supplier->website->uuid);

            if($tenantQuotation = TenantQuotation::where('id', $quotation->internal_id)->first()) {
                $tenantQuotation->update(['st' => Status::CANCELED]);
                $tenantQuotation->items()->update(['st' => Status::CANCELED]);
            }
        });
    }

    /**
     * @param $company
     * @param $contract
     * @param $quotation
     * @return JsonResponse
     */
    public function decline(
        $company,
        $contract,
        $quotation
    ): JsonResponse
    {
        if (auth()->user()->companies()->where('company_user.company_id', $company)->exists()) {
            $contract = Contract::where([
                ['id', $contract],
                ['requester_id', $company]
            ])->with('supplier.website')->first();

            if (!$contract) {
                return response()->json([
                    'message' => __('Contract not found!.'),
                    'status' => Response::HTTP_NOT_FOUND
                ], Response::HTTP_NOT_FOUND);
            }

            if (in_array($contract->st, [Status::REJECTED, Status::CANCELED], true)) {
                return response()->json([
                    'message' => __('The quotation has been rejected/Cancelled already.'),
                    'status' => Response::HTTP_UNPROCESSABLE_ENTITY
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            $sentQuotation = Quotation::where([
                'external_id' => $quotation,
                'hostname_id' => $contract->receiver_id,
                'company_id' => $company,
            ])->with('supplier.website')->first();

            if (!$sentQuotation) {
                return response()->json([
                    'message' => __('Quotation not found!.'),
                    'status' => Response::HTTP_NOT_FOUND
                ], Response::HTTP_NOT_FOUND);
            }

            switchSupplier($sentQuotation->supplier->website->uuid);
            $quotation = TenantQuotation::where('id', $sentQuotation->internal_id)->first();

            $quotation->update(['st' => Status::REJECTED]);
            $quotation->items()->update(['st' => Status::REJECTED]);

            return response()->json([
                'message' => __('Quotation has been declined successfully.'),
                'status' => Response::HTTP_OK,
            ], Response::HTTP_OK);
        }

    }

    /**
     * @param $quotation
     * @param $company
     * @throws GuzzleException
     */
    public function sendQuotationBack(
        $quotation,
        $company
    ): void
    {
        $body['quotation'] = $quotation;

        if ($company->authorization && $company->authorization['type'] === 'password') {
            $body['username'] = $company->authorization['username'];
            $body['password'] = $company->authorization['password'];
        }

        if ($company->authorization && $company->authorization['type'] === 'Bearer') {
            $headers = [
                'Accept' => 'application/json',
                'Authorization' => $company->authorization['type'] . ' ' . $company->authorization['token'],
                'Content-Type' => 'application/json'
            ];
        }
        $this->base_uri = $company->url;
        $this->makeRequest(
            'POST',
            $company->url,
            formParams: $body,
            headers: $headers,
            forceJson: true
        );
    }
}
