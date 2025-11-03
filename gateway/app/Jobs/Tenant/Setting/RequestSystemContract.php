<?php

namespace App\Jobs\Tenant\Setting;

use App\Enums\ContractType;
use App\Foundation\ContractManager\Facades\ContractManager;
use App\Models\Domain;
use App\Models\User;
use Hyn\Tenancy\Models\Website;
use Illuminate\Bus\Queueable;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Http\Request;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class RequestSystemContract
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public Request $request,
        public Website $website,
        public Hostname $tenant
    ) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $contract = ContractManager::create([
            'receiver_id' => 1,
            'receiver_type' => User::class,
            'receiver_connection' => 'cec',

            'requester_type' => Domain::class,
            'requester_connection' => $this->website->uuid,
            'requester_id' => $this->tenant->id,
            'type' => ContractType::INTERNAL,
        ]);


        $this->request->merge([
            'contract_id' => $contract->id
        ]);
    }
}
