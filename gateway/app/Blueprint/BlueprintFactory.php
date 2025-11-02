<?php

namespace App\Blueprint;

use App\Blueprint\Contract\BlueprintFactoryInterface;
use App\Models\Tenants\Product;

class BlueprintFactory implements BlueprintFactoryInterface
{
    public bool $hasBlueprint;

    protected ?array $processor;

    public mixed $blueprint;

    public mixed $node;

    public mixed $in;

    public mixed $out;

    public mixed $pipeline;

    /**
     * @param Product $product
     * @return self
     */
    public function init(
        Product $product
    ): self
    {
        $this->blueprint = $product->blueprint->first();
        $this->hasBlueprint = (bool)$this->blueprint;
        return $this;

//        if($product->hasBlueprint){
//            $this->blueprint = optional($product)->blueprint;
//            $this->hasBlueprint = $product->hasBlueprint;
//            return  $this;
//        }
//        return  $this;
    }

    public function processors(
        $node = 1
    ): BlueprintFactory
    {
        $this->processor = $this->blueprint?->configuration;

        return $this;
    }

    protected function buildProcessor(
        int   $node,
        array $in,
        array $out,
        array $pipeline
    ): void
    {
        $this->node = $node;
        $this->in = $in;
        $this->out = $out;
        $this->pipeline = $pipeline;
    }

    /**
     * @return BlueprintFactory|null
     */
    public function run(): ?BlueprintFactory
    {
        // get the node status from db and level

        if ($this->hasBlueprint) {
            return $this->in()
                ->pipeline()
                ->out();
        }
        return null;
    }

    /**
     * @return $this
     */
    protected function in(): BlueprintFactory
    {
//        $this->in = (new InBlueprintFactory())->run(...$this->in);
        return $this;
    }

    /**
     * @return $this
     */
    public function pipeline(): BlueprintFactory
    {
        return $this;
//        return (new PipelineBlueprintFactory())->pipeline($this->processor);
    }

    /**
     * @return $this
     */
    public function out(): BlueprintFactory
    {
        return $this;
//        return (new OutBlueprintFactory())->out($this->processor);
    }


}
