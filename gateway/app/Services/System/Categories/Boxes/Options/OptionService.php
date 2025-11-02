<?php


namespace App\Services\System\Categories\Boxes\Options;


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
     * @param string $category
     * @param string $box
     * @return string
     * @throws GuzzleException
     */
    final public function obtainOptionsByCategoryAndBox(
        string $category,
        string $box
    )
    {
        return $this->makeRequest('get', "categories/{$category}/boxes/{$box}/options");
    }

    /**
     * @param string $category
     * @param string $box
     * @param string $option
     * @return string
     * @throws GuzzleException
     */
    final public function obtainOptionByCategoryAndBox(
        string $category,
        string $box,
        string $option
    )
    {
        return $this->makeRequest('get', "categories/{$category}/boxes/{$box}/options/{$option}");
    }

    /**
     * @param string $category
     * @param string $box
     * @param array  $request
     * @return string
     * @throws GuzzleException
     */
    final public function storeOptionByCategoryAndBox(
        string $category,
        string $box,
        array  $request
    )
    {
        return $this->makeRequest('post', "categories/{$category}/boxes/{$box}/options", [], $request);
    }

    /**
     * @param string $category
     * @param string $box
     * @param string $option
     * @param array  $request
     * @return string
     * @throws GuzzleException
     */
    final public function updateOptionByCategoryAndBox(
        string $category,
        string $box,
        string $option,
        array  $request
    )
    {
        return $this->makeRequest('put', "categories/{$category}/boxes/{$box}/options/{$option}", [], $request);
    }

    /**
     * @param string $category
     * @param string $box
     * @param string $option
     * @return string
     * @throws GuzzleException
     */
    final public function deleteOptionByCategoryAndBox(
        string $category,
        string $box,
        string $option
    )
    {
        return $this->makeRequest('delete', "categories/{$category}/boxes/{$box}/options/{$option}");
    }


    /**
     * @param string $category
     * @param string $box
     * @param string $option
     * @param array  $request
     * @return string
     * @throws GuzzleException
     */
    final public function obtainAttachSystemOptions(
        string $category,
        string $box,
        string $option,
        array  $request
    )
    {
        return $this->makeRequest("post", "/categories/{$category}/boxes/{$box}/options/{$option}/attach", [], $request);
    }

}
