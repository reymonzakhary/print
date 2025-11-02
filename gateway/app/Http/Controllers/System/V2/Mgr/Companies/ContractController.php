<?php

declare(strict_types=1);

namespace App\Http\Controllers\System\V2\Mgr\Companies;

use App\Enums\ContractType;
use App\Foundation\ContractManager\Facades\ContractManager;
use App\Foundation\Status\Status;
use App\Http\Controllers\Controller;
use App\Http\Controllers\System\Mgr\Clients\ClientController;
use App\Http\Controllers\System\V2\Mgr\Tenant\TenantController;
use App\Http\Controllers\Tenant\Mgr\Users\UserController;
use App\Http\Requests\Clients\ClientRequest;
use App\Http\Requests\Contracts\UpdateContractRequest;
use App\Http\Requests\Suppliers\InviteSupplierRequest;
use App\Http\Requests\Users\StoreUserRequest;
use App\Http\Resources\Suppliers\ContractResource;
use App\Mail\CompanyInvitationInteraction;
use App\Mail\SupplierCredentialsMail;
use App\Mail\SupplierInvitationMail;
use App\Models\Company;
use App\Models\Contract;
use App\Models\Hostname;
use App\Models\Tenants\Context;
use App\Models\Tenants\Team;
use App\Models\Tenants\User;
use App\Repositories\UserRepository;
use Carbon\Carbon;
use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Application;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class ContractController extends Controller
{
    protected $userRepository;
    public function __construct(
        User $user
    )
    {
        $this->userRepository = new UserRepository($user);
    }

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
     * Invite a supplier by creating or finding their hostname, establishing a contract,
     * creating a user account in the supplier's tenant, and sending an invitation email.
     *
     * @param InviteSupplierRequest $request
     * @param Int $company
     * @return JsonResponse
     * @throws Exception
     */
    public function store(
        InviteSupplierRequest $request,
        int $company,
    ): JsonResponse
    {
        if (!auth()->user()->companies()->where('company_user.company_id', $company)->exists()) {
            return response()->json([
                "message" => __("Company not found!"),
                "status" => Response::HTTP_NOT_FOUND
            ], Response::HTTP_NOT_FOUND);
        }
        // Find existing hostname by COC number or domain
        $hostname = Hostname::query()
            ->where('custom_fields->coc', $request->coc)
            ->orWhere('custom_fields->domain', $request->domain)
            ->with('website')
            ->first();

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

            // Retrieve the newly created hostname
            $hostname = Hostname::query()
                ->with('website')
                ->where('fqdn', $request->fqdn)
                ->first();
        }

        // Check if a contract already exists between the companies
        $contract = ContractManager::getContractWithCompany(
            $hostname->website->uuid,
            $hostname->id,
            $request->user()->company->id
        );
        // If contract exists, inform user they already have a relationship
        if ($contract) {
            return response()->json([
                'message' => __('You already have contract with this supplier.'),
                'status' => Response::HTTP_UNPROCESSABLE_ENTITY,
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        // Store email sender details for later use
        $mail_from = auth()->user()->company?->email ?? config('mail.from')['address'];
        $company_name = auth()->user()->company?->name;

        // Create a new contract with the supplier
        $contract = ContractManager::createWithExternal(
            Company::class,
            $request->user()->company->id,
            Hostname::class,
            $hostname->id,
            [
                'receiver_connection' => $hostname->website->uuid,
                'callback' => $request->callback_url,
                'st' => \App\Enums\Status::PENDING->value,
                'active' => false,
                'custom_fields' => [
                    'contact_person' => [
                        [
                            'email' => $request->email,
                            'name' => $request->first_name . ' ' . $request->last_name,
                            'email_send' => false,
                            'password_send' => false,
                            'created_at' => now()
                        ]
                    ],
                ]
            ]
        );

        // Switch to supplier's tenant context to create/check user
        switchSupplier($hostname->website->uuid);

        // Check if user already exists in supplier's tenant
        $user = User::query()->where('email', $request->email)->first();

        // Create user in supplier's tenant if they don't exist
        if (!$user) {
            $team = Team::query()->firstOrCreate(
                ['name' => 'account_manager'],
                ['display_name' => 'Account Manager']
            );

            $userPayload = [
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'gender' => $request->gender,
                'email' => $request->email,
                'password' => Str::password(),
                'type' => 'individual',
                'ctx_id' => 1,
                'member' => false,
                'roles' => ['quotation_supplier'],
                'teams' => [$team],
                'generated_password' => false
            ];

            $user = $this->userRepository->create($userPayload);
        }

        // Prepare data for signed URL generation
        $url_data = [
            'hostname' => $hostname->host_id,
            'company' => $request->user()->company->id,
            'contract' => $contract->id,
            'do' => 1,
            'xp' => $user->id . 'xp09364h87n4'
        ];

        // Generate acceptance URL (valid for 6 days)
        $accept_url = URL::temporarySignedRoute(
            'suppliers.invitation',
            Carbon::now()->addDays(6),
            $url_data
        );

        // Generate rejection URL (valid for 6 days)
        $url_data['do'] = 0;
        $reject_url = URL::temporarySignedRoute(
            'suppliers.invitation',
            Carbon::now()->addDays(6),
            $url_data
        );

        // Send invitation email to the supplier contact
        Mail::alwaysFrom($mail_from, $company_name);
        $mailable = new SupplierInvitationMail(
            $request->user()->company,
            $accept_url,
            $reject_url,
            implode(" ", $request->only('first_name', 'last_name'))
        );
        $mailable->subject(__(
            'mails.invitation_subject',
            [
                'company' => $request->user()->company->name
            ]
        ));

        Mail::to($request->email)
            ->cc(['info@prindustry.com'])
            ->send($mailable);

        // Mark email as sent in contract custom fields
        $customFields = $contract->custom_fields;
        $contactPersons = collect($customFields['contact_person'] ?? [])
            ->map(function ($contact) use ($request) {
                if ($contact['email'] === $request->email) {
                    $contact['email_send'] = true;
                }
                return $contact;
            })->toArray();

        $customFields['contact_person'] = $contactPersons;
        $contract->custom_fields = $customFields;
        $contract->save();
        // Return success response with contract details
        return response()->json([
            'message' => __('Supplier has been invited, waiting for acceptation.'),
            'callback' => $request->callback_url,
            'status' => Response::HTTP_ACCEPTED,
            'company' => $request->user()->company->id,
            'contract' => ContractResource::make($contract->load('supplier'))
        ], Response::HTTP_ACCEPTED);
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
     * @param Request $request
     * @param Hostname $hostname
     * @param Company $company
     * @return RedirectResponse|never|void
     * @throws AuthorizationException
     */
    public function invitation(
        Request  $request,
        Hostname $hostname,
        Company  $company
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
     * Accept a supplier invitation by activating the contract and sending verification emails.
     *
     * @param Contract $contract The contract to be accepted
     * @param Company $company The company accepting the invitation
     * @param string $tenant_url The tenant's FQDN for redirection
     * @return RedirectResponse
     * @throws AuthorizationException
     */
    private function accept(
        Contract $contract,
        Company $company,
        string $tenant_url
    ): RedirectResponse
    {
        // Find the tenant hostname and switch to supplier's tenant context
        $tenant = Hostname::query()
            ->where('fqdn', $tenant_url)
            ->with('website')
            ->first();

        switchSupplier($tenant->website->uuid);

        // Extract user ID from the encrypted parameter and find the user
        $userId = Str::before(request()->input('xp'), 'xp');
        $user = User::query()->findOrFail($userId);
        $password = Str::password(15);
        $user?->update(['password' => $password]);
        // Abort if contract is not pending or user doesn't exist
        if ($contract->st !== Status::PENDING || !$user) {
            abort(403, 'Invalid contract status or user not found.');
        }

//         Activate the contract
        $contract->update([
            'st' => Status::ACCEPTED,
            'active' => true,
            'activated_at' => Carbon::now()
        ]);

        // Get custom fields and find the contact person
        $customFields = $contract->custom_fields;
        $contactPerson = collect($customFields['contact_person'] ?? [])
            ->firstWhere('email', $user->email);

        // Send email verification if not already sent
        if ($contactPerson && !$contactPerson['password_send']) {
            // Send API email verification notification to the user

            Mail::to($contactPerson['email'])
                ->send(
                    new SupplierCredentialsMail(
                        $contactPerson['email'],
                        $password,
                        $contactPerson['name'],
                        $contract->supplier->fqdn
                    )
                );
            $user->forceFill(['email_verified_at' => Carbon::now()->format('Y-m-d g:i:s')]);

            $user->save();
            $contract->supplier->forceFill(['custom_fields->email_send' => true])->save();

            // Update the contact person's email_send status to true
            $contactPersons = collect($customFields['contact_person'] ?? [])
                ->map(function ($contact) use ($user) {
                    if ($contact['email'] === $user->email) {
                        $contact['password_send'] = true;
                    }
                    return $contact;
                })->toArray();

            $customFields['contact_person'] = $contactPersons;
            $contract->custom_fields = $customFields;
            $contract->save();
        }

        // Notify the company that the invitation was accepted
        $notificationData = array_merge(
            $contract->supplier->custom_fields->toArray(),
            $user->toArray(),
            ['company' => $company->name]
        );


        unset($notificationData['password']);
        $mailable = new CompanyInvitationInteraction('accepted', $notificationData);
        $mailable->subject(__("mails.invitation_subject" ,[
                "company" =>  $notificationData['company_name'],
                "status" => __("accepted")
            ])
        );

        Mail::to($company->email)
            ->send($mailable);

        // Determine the URL scheme based on environment
        $scheme = app()->environment('local') ? 'http' : 'https';

        // Redirect to the supplier's login page
        return redirect()->to("{$scheme}://{$tenant_url}/manager/login");
    }

    /**
     * @param $contract
     * @param $company
     * @param $tenant_url
     * @return \Illuminate\Contracts\Foundation\Application|Factory|View|Application
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

        // Notify the company that the invitation was accepted
        $custom_fields = array_merge(
            $contract->supplier->custom_fields->toArray(),
            ['company' => $company->name]
        );


        unset($custom_fields['password']);
        $mailable = new CompanyInvitationInteraction('Rejected', $custom_fields);
        $mailable->subject(__("mails.invitation_replied_subject" ,[
                "company" =>  $custom_fields['company_name'],
                "status" => __("Rejected")
            ])
        );
        Mail::to($company->email)
            ->send($mailable);

        return view('emails.rejected_invitation', [
            'custom_fields' => $custom_fields,
            'status' => __("rejected")
        ]);
    }

}
