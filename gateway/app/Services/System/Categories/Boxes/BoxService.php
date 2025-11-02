<?php


namespace App\Services\System\Categories\Boxes;


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
     * @param string $category
     * @return string
     * @throws GuzzleException
     */
    final public function obtainBoxesByCategory(
        string $category
    )
    {
        return $this->makeRequest('get', "categories/{$category}/boxes");
    }

    /**
     * @param string $category
     * @param string $box
     * @return string
     * @throws GuzzleException
     */
    final public function obtainBoxesByCategorySlug(
        string $category,
        string $box
    )
    {
        return $this->makeRequest('get', "categories/{$category}/boxes/{$box}");
    }

    /**
     * @param string $category
     * @param array  $request
     * @return string
     * @throws GuzzleException
     */
    final public function storeBoxesByCategorySlug(
        string $category,
        array  $request
    )
    {
        return $this->makeRequest('post', "categories/{$category}/boxes", [], $request);
    }

    /**
     * @param string $category
     * @param string $box
     * @param array  $request
     * @return string
     * @throws GuzzleException
     */
    final public function updateBoxesByCategorySlug(
        string $category,
        string $box,
        array  $request
    )
    {
        return $this->makeRequest('put', "categories/{$category}/boxes/{$box}", [], $request);
    }

    /**
     * @param string $category
     * @param string $box
     * @return string
     * @throws GuzzleException
     */
    final public function deleteBoxesByCategorySlug(
        string $category,
        string $box
    )
    {
        return $this->makeRequest('delete', "categories/{$category}/boxes/{$box}");
    }

    /**
     * @param string $category
     * @param string $box
     * @param array  $request
     * @return string
     * @throws GuzzleException
     */
    final public function obtainAttachSystemBoxes(
        string $category,
        string $box,
        array  $request
    )
    {
        return $this->makeRequest("post", "/categories/{$category}/boxes/{$box}/attach", [], $request);
    }
}
