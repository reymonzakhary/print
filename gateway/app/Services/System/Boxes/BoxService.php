<?php


namespace App\Services\System\Boxes;

use App\Contracts\ServiceContract;
use App\Utilities\Traits\ConsumesExternalServices;
use GuzzleHttp\Exception\GuzzleException;

class BoxService extends ServiceContract
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
     * @param array $request
     * @return string
     * @throws GuzzleException
     */
    final public function obtainSystemBoxes(
        array $request
    )
    {
        return $this->makeRequest('get', "/boxes", $request);
    }

    /**
     * @return string
     * @throws GuzzleException
     */
    final public function obtainSystemBox(
        string $box
    )
    {
        return $this->makeRequest('get', "/boxes/{$box}");
    }

    /**
     * @param array $request
     * @return string
     * @throws GuzzleException
     */
    final public function storeSystemBoxes(
        array $request
    )
    {
        return $this->makeRequest('post', "boxes", [], $request);
    }

    /**
     * @param string $category
     * @param array  $request
     * @return string
     * @throws GuzzleException
     */
    final public function updateSystemBoxes(
        string $box,
        array  $request
    )
    {
        return $this->makeRequest('put', "boxes/{$box}", [], $request);
    }

    /**
     * @param string $box
     * @param array $request
     * @return string
     * @throws GuzzleException
     */
    final public function deleteSystemBox(
        string $box,
        array  $request
    )
    {
        return $this->makeRequest('delete', "boxes/{$box}", $request);
    }

    /**
     * @param string $slug
     * @param array  $request
     * @return string
     * @throws GuzzleException
     */
    final public function obtainAttachSystemBoxes(
        string $slug,
        array  $request
    )
    {
        return $this->makeRequest('post', "boxes/{$slug}/attach", [], $request);
    }

    /**
     * @param string $slug
     * @param array $request
     * @return string
     * @throws GuzzleException
     */
    final public function obtainDetachSystemBoxes(
        string $slug,
        array  $request
    )
    {
        return $this->makeRequest('post', "boxes/{$slug}/detach", [], $request);
    }

    /**
     * @param string $slug
     * @param array  $request
     * @return string
     * @throws GuzzleException
     */
    final public function obtainSystemBoxRelations(
        string $slug,
        array  $request
    )
    {
        return $this->makeRequest('get', "boxes/{$slug}/relations", $request);
    }

    /**
     * @return string
     * @throws GuzzleException
     */
    final public function obtainUnmatchedSystemBoxes()
    {
        return $this->makeRequest('get', "unmatched/boxes");
    }

    /**
     * @param string $box
     * @return null|string|array|object
     * @throws GuzzleException
     */
    final public function deleteUnmatchedSystemBoxes(
        string $box
    ): null|string|array|object
    {
        return $this->makeRequest('delete', "unmatched/boxes/{$box}");
    }

    /**
     * @param array $request
     * @return string
     * @throws GuzzleException
     */
    final public function obtainUnlinkedBoxes(
        array $request
    )
    {
        return $this->makeRequest("GET", "unlinked/boxes", $request);
    }

    /**
     * @param array $request
     * @param array $params
     * @return string
     * @throws GuzzleException
     */
    final public function mergeSystemBoxes(
        array $request,
        array $params = []
    )
    {
        return $this->makeRequest("POST", "merge/boxes", $params, $request);
    }


    /**
     * @return string
     * @throws GuzzleException
     */
    final public function matchedSystemBoxes()
    {
        return $this->makeRequest("GET", "matched/boxes");
    }

}
