<?php

namespace App\Services\DesignProviders;

use App\Contracts\ServiceContract;
use App\Utilities\Traits\ConsumesExternalServices;

final class ConneoPreflightService extends ServiceContract
{
    use ConsumesExternalServices;

    private string $conneo_id;

    private string $conneo_auth_token;

    /**
     * set conneo base service uri, id, and auth token
     */
    public function __construct()
    {
        $this->base_uri = config('services.conneo.base_uri');
        $this->conneo_id = config('services.conneo.id');
        $this->conneo_auth_token = config('services.conneo.auth_token');
    }

    /** 
     * creates session with conneo preflight
     * @param array $data
     * @return array|string
    */
    final public function obtainSession(
        array $data
    )
    {
        $data['conneoId'] = $this->conneo_id;

        return $this->makeRequest(
            'post',
            $this->base_uri.'/api/'.$this->conneo_id.'/sessions',
            [],
            $data,
            ['Authorization' => $this->conneo_auth_token],
            false,
            true
        );
    }

    final public function obtainProduct(
        $session_id
    )
    {
        return $this->makeRequest(
            'get',
            $this->base_uri.'/api/'.$this->conneo_id.'/'.$session_id.'/products',
            [],
            [],
            ['Authorization' => $this->conneo_auth_token],
            false,
            true
        );
    }

}
