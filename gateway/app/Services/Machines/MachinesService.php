<?php

namespace App\Services\Machines;

use App\Contracts\ServiceContract;
use App\Utilities\Traits\ConsumesExternalServices;
use GuzzleHttp\Exception\GuzzleException;

class MachinesService extends ServiceContract
{
    use ConsumesExternalServices;

    /**
     * Class Constructor
     *
     * Initializes a new instance of the class.
     *
     * @return void
     */
    public function __construct()
    {
        $this->base_uri = config('services.machines.base_uri');
        $this->tenant_id = optional(request()->tenant)->uuid;
        $this->tenant_name = optional(request()->hostname)->fqdn;
    }

    /**
     * Retrieve a list of machines.
     *
     * @return string|array
     * @throws GuzzleException
     */
    final public function obtainMachines(): string|array
    {
        return $this->makeRequest(
            method:'get',
            requestUrl: "suppliers/{$this->tenant_id}/machines"
        );
    }

    /**
     * Retrieve a single machine.
     *
     * @param string $machine_id
     * @return string|array
     * @throws GuzzleException
     */
    final public function obtainMachine(
        string $machine_id
    ): string|array
    {
        return $this->makeRequest(
            method:'get',
            requestUrl: "suppliers/{$this->tenant_id}/machines/$machine_id"
        );
    }

    /**
     * Create a new machine.
     *
     * @param array $request
     * @return string|array
     * @throws GuzzleException
     */
    final public function obtainCreateMachine(
        array $request
    ): string|array
    {
        return $this->makeRequest(
            method: 'post',
            requestUrl: "suppliers/{$this->tenant_id}/machines",
            formParams:  array_merge($request,['tenant_name' => $this->tenant_name]),
            forceJson: true
        );
    }

    /**
     * Update a machine with the given parameters.
     *
     * @param string $machine The machine details.
     * @param array  $request The request details.
     * @param array  $options
     * @return string|array The response from the API.
     *
     * @throws GuzzleException
     */
    final public function obtainUpdateMachine(
        string $machine,
        array $request,
        array $options
    ): string|array
    {
        return $this->makeRequest(
            method: 'put',
            requestUrl: "suppliers/{$this->tenant_id}/machines/{$machine}",
            formParams: [
                'tenant_name' => $this->tenant_name,
                'options' => $options,
                'machine' => $request
            ],
            forceJson: true
        );
    }

    /**
     * Destroy a machine.
     *
     * @param string $machine
     * @return array|string
     * @throws GuzzleException
     */
    final public function obtainDestroyMachine(
        string $machine
    ): array|string
    {
        return $this->makeRequest(
            method: 'delete',
            requestUrl: "suppliers/{$this->tenant_id}/machines/$machine"
        );
    }

}
