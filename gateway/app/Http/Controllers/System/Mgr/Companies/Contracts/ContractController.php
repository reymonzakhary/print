<?php

namespace App\Http\Controllers\System\Mgr\Companies\Contracts;

use App\Enums\ContractType;
use App\Foundation\Status\Status;
use App\Http\Controllers\Controller;
use App\Http\Controllers\System\Mgr\Clients\ClientController;
use App\Http\Requests\Clients\ClientRequest;
use App\Http\Requests\Contracts\UpdateContractRequest;
use App\Http\Requests\Suppliers\InviteSupplierRequest;
use App\Http\Resources\Suppliers\ContractResource;
use App\Mail\CompanyInvitationInteraction;
use App\Mail\SupplierCredentialsMail;
use App\Mail\SupplierInvitationMail;
use App\Models\Company;
use App\Models\Contract;
use App\Models\Hostname;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Symfony\Component\HttpFoundation\Response;

class ContractController extends Controller
{

    /**
     * @param int $company
     * @return mixed
     */
    public function index(
        int $company
    ): mixed
    {
        if (auth()->user()->companies()->where('company_user.company_id', $company)->exists()) {
            return ContractResource::collection(
                auth()->user()->companies()->where('company_user.company_id', $company)->first()?->contracts->load('supplier')
                    ->where('supplier.ready', true)
            )->additional([
                'message' => null,
                'status' => Response::HTTP_OK
            ]);
        }

        return response()->json([
            "message" => __("Company not found!"),
            "status" => Response::HTTP_NOT_FOUND
        ], Response::HTTP_NOT_FOUND);
    }

    /**
     * @param InviteSupplierRequest $request
     * @return JsonResponse
     */
    public function store(
        InviteSupplierRequest $request
    )
    {
        $hostname = Hostname::query()->where('custom_fields->coc', $request->coc)
            ->orWhere('custom_fields->domain', $request->domain)->with('website')->first();

        if (!$hostname) {

            $data = $request->validated();
            $data['company_name'] = $request->company;
            $data['role'] = 'quotation_supplier';
            $data['namespaces'] = $request->namespaces;
            $data['fqdn'] = $request->fqdn;
            $data['company_coc'] = $request->coc;
            $data['domain'] = $request->domain;
            $data['password'] = $request->password;
            $data['tax_nr'] = $request->tax_nr;
            $data['primary'] = $request->primary;
            $data['manager_language'] = $request->manager_language;

            $req = new ClientRequest($data);
            app(ClientController::class)->store($req);

            $hostname = Hostname::query()->with('website')->where('fqdn', $request->fqdn)->first();
        }

        $contract = Contract::where([
            ['receiver_type', Hostname::class],
            ['receiver_connection', $hostname->website->uuid],
            ['receiver_id', $hostname->id],
            ['requester_type', Company::class],
            ['requester_id', $request->user()->company->id]
        ])->first();
        // create contract if no contract
        if (!$contract) {
            $contract = Contract::create([
                'receiver_id' => $hostname->id,
                'receiver_connection' => $hostname->website->uuid,
                'receiver_type' => Hostname::class,
                'requester_id' => $request->user()->company->id,
                'requester_type' => Company::class,
                'callback' => request()->callback_url,
                'st' => Status::PENDING,
                'active' => false,
                'type' => ContractType::EXTERNAL->value
            ]);
            // prepare data for url generator
            $url_data = [
                'hostname' => $hostname->host_id,
                'company' => $request->user()->company->id,
                'contract' => $contract->id,
                'do' => 1
            ];
            // generate acceptance url
            $accept_url = URL::temporarySignedRoute(
                'suppliers.invitation',
                Carbon::now()->addDays(6),
                $url_data
            );
            // generate rejection url
            $url_data['do'] = 0;
            $reject_url = URL::temporarySignedRoute(
                'suppliers.invitation',
                Carbon::now()->addDays(6),
                $url_data
            );


            // send invitation email
            Mail::alwaysFrom(
                auth()->user()->company?->email??config('mail.from')['address'], auth()->user()->company?->name);
            Mail::to($hostname->custom_fields->pick('email'))
                ->cc(['ramon@prindustry.com'])
                ->send(
                new SupplierInvitationMail(
                    $request->user()->company,
                    $accept_url,
                    $reject_url,
                    implode(" ", $request->only('first_name', 'last_name'))
                )
            );

            return response()->json([
                'message' => __('Supplier has been invited, waiting for acceptation.'),
                'callback' => $request->callback_url,
                'status' => Response::HTTP_ACCEPTED,
                'company' => $request->user()->company->id,
                'contract' => ContractResource::make($contract->load('supplier'))
            ], Response::HTTP_ACCEPTED);
        }

        return response()->json([
            'message' => __('You already have contract with this supplier.'),
            'status' => Response::HTTP_UNPROCESSABLE_ENTITY,
        ], Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    /**
     * @param UpdateContractRequest $request
     * @param                       $company
     * @param                       $contract
     * @return JsonResponse
     */
    public function update(
        UpdateContractRequest $request,
                              $company,
                              $contract
    )
    {
        if ($contract = auth()->user()->company->contracts()->where('contracts.id', $contract)->first()) {
            $contract->update($request->validated());

            return response()->json([
                "message" => __("Contract updated successfully."),
                "status" => Response::HTTP_OK
            ], Response::HTTP_OK);
        }

        return response()->json([
            "message" => __("Contract not found!"),
            "status" => Response::HTTP_NOT_FOUND
        ], Response::HTTP_NOT_FOUND);
    }

    /**
     * @param Request  $request
     * @param Hostname $hostname
     * @param Company  $company
     * @return RedirectResponse|never|void
     */
    public function invitation(
        Request  $request,
        Hostname $hostname,
        Company  $company,
        Contract $contract
    )
    {

        $contract = Contract::where([
            'id' =>  $request->query('contract'),
            'requester_id' => $company->id,
            'receiver_id' => $hostname->id,
        ])->first();

        return match ((int)$request->query('do')) {
            0 => $this->reject($contract, $company, $hostname->fqdn),
            1 => $this->accept($contract, $company, $hostname->fqdn),
            default => abort(404),
        };
    }

    /**
     * @param $contract
     * @param $company
     * @param $tenant_url
     * @return RedirectResponse
     */
    private function accept(
        $contract,
        $company,
        $tenant_url
    )
    {
        if ($contract->st !== Status::PENDING) {
            abort(403);
        }

        $contract->update([
            'st' => Status::ACCEPTED,
            'active' => true,
            'activated_at' => Carbon::now()
        ]);

        if (!$contract->supplier->custom_fields->pick('email_send')) {
            // need to after teh accept
            Mail::to($contract->supplier->custom_fields->pick('email'))
                ->send(
                    new SupplierCredentialsMail(
                        $contract->supplier->custom_fields->pick('email'),
                        $contract->supplier->custom_fields->pick('password'),
                        $contract->supplier->custom_fields->pick('name'),
                        $contract->supplier->fqdn
                    )
                );
            $contract->supplier->forceFill(['custom_fields->email_send' => true])->save();
        }

        // send password and email
        Mail::to($company->email)->send(
            new CompanyInvitationInteraction("accepted", $contract->supplier->custom_fields->toArray())
        );

        $scheme = env('APP_ENV') === 'local' ? 'http' : 'https';

        return redirect()->to("{$scheme}://{$tenant_url}/manager/login");
    }

    /**
     * @param $contract
     * @param $company
     * @param $tenant_url
     * @return RedirectResponse
     */
    private function reject(
        $contract,
        $company,
        $tenant_url
    )
    {
        if ($contract->st !== Status::PENDING) {
            abort(403);
        }

        $contract->update([
            'st' => Status::REJECTED,
            'active' => false,
            'activated_at' => null
        ]);

        Mail::to($company->email)->send(new CompanyInvitationInteraction("Rejected", $contract->supplier->custom_fields));

        $scheme = env('APP_ENV') === 'local' ? 'http' : 'https';

        return redirect()->to("{$scheme}://{$tenant_url}");
    }

}
