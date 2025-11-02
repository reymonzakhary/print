<?php


namespace App\Services\System\Options;

use App\Contracts\ServiceContract;
use App\Utilities\Traits\ConsumesExternalServices;
use GuzzleHttp\Exception\GuzzleException;

class OptionService extends ServiceContract
{
    use ConsumesExternalServices;

    /**
     * @inheritDoc
     */
    public function __construct()
    {
        $this->base_uri = config('services.categories.base_uri');
    }

    /**
     * @return string
     * @throws GuzzleException
     */
    final public function obtainSystemOptions(
        array $request
    )
    {
        return $this->makeRequest('get', "/options", $request);
    }

    /**
     * @return string
     * @throws GuzzleException
     */
    final public function obtainSystemOption(
        string $option
    )
    {
        return $this->makeRequest('get', "/options/{$option}");
    }

    /**
     * @param array $request
     * @return string
     * @throws GuzzleException
     */
    final public function storeSystemOptions(
        array $request
    ): string|array
    {
        return $this->makeRequest('post', "options", [], $request);
    }

    /**
     * @param string $option
     * @param array $request
     * @return string
     * @throws GuzzleException
     */
    final public function updateSystemOptions(
        string $option,
        array  $request
    )
    {
        return $this->makeRequest('put', "options/{$option}", [], $request);
    }

    /**
     * @param string $option
     * @param array $request
     * @return string
     * @throws GuzzleException
     */
    final public function deleteSystemOption(
        string $option,
        array $request
    )
    {
        return $this->makeRequest('delete', "options/{$option}", $request);
    }

    /**
     * Make a POST request to attach system options.
     *
     * @param string $slug The slug of the option.
     * @param array $request The data to be attached.
     * @return mixed The response from the API.
     * @throws GuzzleException
     */
    final public function obtainAttachSystemOptions(
        string $slug,
        array  $request
    )
    {
        return $this->makeRequest('post', "options/{$slug}/attach", [], $request);
    }

    /**
     * @param string $slug
     * @param array $request
     * @return mixed
     * @throws GuzzleException
     */
    final public function obtainDetachSystemOptions(
        string $slug,
        array  $request
    )
    {
        return $this->makeRequest('post', "options/{$slug}/detach", [], $request);
    }

    /**
     * @param string $slug
     * @param array  $request
     * @return string
     * @throws GuzzleException
     */
    final public function obtainSystemOptionRelations(
        string $slug,
        array  $request
    )
    {
        return $this->makeRequest('get', "options/{$slug}/relations", $request);
    }

    /**
     * @return string
     * @throws GuzzleException
     */
    final public function obtainUnmatchedSystemOptions()
    {
        return $this->makeRequest('get', "/unmatched/options");
    }

    /**
     * @param string $option
     * @return array|object|string|null
     * @throws GuzzleException
     */
    final public function deleteUnmatchedSystemOptions(
        string $option
    ): object|array|string|null
    {
        return $this->makeRequest('delete', "unmatched/options/{$option}");
    }

    /**
     * @param array $request
     * @return string
     * @throws GuzzleException
     */
    final public function obtainUnlinkedOptions(
        array $request
    )
    {
        return $this->makeRequest('GET', "unlinked/options", $request);
    }


    /**
     * Retrieve matched options
     *
     * @return string
     * @throws GuzzleException
     */
    final public function obtainMatchedOptions()
    {
        return $this->makeRequest('GET', "matched/options");
    }

    /**
     * @param array $request
     * @param array $params
     * @return string
     * @throws GuzzleException
     */
    final public function mergeSystemOptions(
        array $request,
        array $params = []
    )
    {
        return $this->makeRequest("POST", "merge/options", $params, $request);
    }
}
