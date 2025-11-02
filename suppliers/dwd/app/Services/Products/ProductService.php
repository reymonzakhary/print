<?php

namespace App\Services\Products;

class ProductService
{

    /**
     * The category boops.
     *
     * @var array
     */
    public $cats = [];

    /**
     * properties to loop upon.
     *
     * @var array
     */
    private $boxes = [];

    /**
     * excluded options.
     *
     * @var array
     */
    private $excludes = [];

   /**
     * Create a new service instance.
     *
     * @param object $boxes
     * @param array $excludes
     * @return void
     */
    public function __construct(
        object $boxes,
        array $excludes
    )
    {
        $this->boxes = $boxes;

        $this->excludes = $excludes;

        $this->handle();
    }

     /**
     * Execute the service.
     *
     * @return ProductService $this
     */
    public function handle()
    {
        foreach($this->boxes as $boops)
        {

            $this->cats[$boops->title] = $boops->options->pluck('title')->toArray();
        }

        foreach($this->excludes['boxes'] as $option)
        {
            unset($this->cats[$option]);
        }

        foreach($this->excludes['options'] as $oKey => $oValue)
        {
            $index = array_search($oValue, $this->cats[$oKey]);
            unset($this->cats[$oKey][$index]);
        }

        return $this;
    }
}
