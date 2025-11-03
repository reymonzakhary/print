<?php

namespace App\Jobs\Tenant\Setting;

use App\Enums\ContractType;
use App\Enums\Status;
use App\Foundation\ContractManager\Facades\ContractManager;
use App\Models\Domain;
use Illuminate\Bus\Queueable;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Http\Request;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;

class RequestContract
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public Request $request
    ) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $hostname = Domain::with('website')->find($this->request->recipient_hostname);
        $data = [
            'receiver_connection' => $hostname->website->uuid,
            'requester_connection' => tenant()->uuid,
        ];

        // Handle External Suppliers (dwd , etc )
        if($hostname->website->external && $hostname->website->configure) {
            $auth_values = [];
            foreach (array_keys($hostname->website->configure['auth']) as $key) {
                $auth_values[$key] = $this->request->input($key);
            }
            $data['custom_fields']['auth'] = $auth_values;
            $contract = ContractManager::createWithExternal(
                Domain::class ,  domain()->id , Domain::class , $hostname->id ,
                additionalData: $data
            );
        } else {
            $contract = ContractManager::createBetween(
                    Domain::class ,  domain()->id , Domain::class , $hostname->id ,
                additionalData: $data
            );
        }
        $this->request->merge([
            'contract_id' => $contract->id
        ]);
    }
}
