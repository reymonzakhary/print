<?php

namespace App\Services\Tenant\FM;

use App\Contracts\ServiceContract;
use App\Utilities\Traits\ConsumesExternalServices;
use GuzzleHttp\Exception\GuzzleException;

class FileManagerService extends ServiceContract
{
    use ConsumesExternalServices;

    /**
     * CategoryService constructor.
     */
    public function __construct()
    {
        $this->base_uri = config('services.fm.base_uri');
        $this->tenant_id = optional(request()->tenant)->uuid;
    }

    /**
     * @param array $request
     * @return array|string
     * @throws GuzzleException
     */
    public function zip(
        array $request = []

    )
    {
        return $this->makeRequest('post', 'zip', [], $request);

    }

    /**
     * @param array $request
     * @return array|string
     * @throws GuzzleException
     */
    final public function extract(
        array $request = []
    )
    {
        return $this->makeRequest('post', 'zip/extract', [], $request);
    }

    /**
     * @param array $request
     * @return array|string
     * @throws GuzzleException
     */
    final public function rename(
        array $request = []
    )
    {
        return $this->makeRequest('post', 'directory/rename', [], $request);
    }

    /**
     * @param array $request
     * @return string
     * @throws GuzzleException
     */
    final function readExcel(
        array $request = []
    )
    {
        return $this->makeRequest('post', 'excel/read', [], $request);
    }

    /**
     * @param string $from
     * @param string $to
     * @return string
     * @throws GuzzleException
     */
    final public function copyDirectory(
        string $from,
        string $to
    )
    {
        return $this->makeRequest('post', 'copy/directory', [], [
            'from' => $from,
            'to' => $to
        ]);
    }
}
