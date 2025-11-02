<?php

namespace App\Plugins\Abstracts;

use App\Plugins\Concrete\PluginPipelineContractInterface;
use Illuminate\Database\Eloquent\Model;

abstract class PluginPipelineAbstract implements PluginPipelineContractInterface
{

    /**
     * The step id for the pipeline.
     * @var int
     */
    public int $id;

    /**
     * The type are the booted trait for the pipeline.
     * @var string
     */
    public mixed $type;

    /**
     * The event is object or array.
     * @var array|object
     */
    public object|array $event;

    /**
     * The action is process what will be make on the running step.
     * @var array|object
     */
    public array|object $action;

    /**
     * The active is to let know that this step is disabled.
     * @var bool
     */
    public bool $active;

    /**
     * The assets are a list of storage data on s3 disk or local.
     * @var array
     */
    public array $assets;

    /**
     * The decision is the step to make a decision of switching the flow.
     * @var bool
     */
    public bool $decision;

    /**
     * The queueable is the Async process for the pipeline.
     * @var bool
     */
    public bool $queueable;

    /**
     * The transition is the object who runs on times or cron-jobs.
     * @var object|array
     */
    public array|object $transition;

    /**
     * The QueueItem what is stored into the db.
     * @var Model
     */
    public Model $job;

    /**
     * The Queue is the main model holding the whole pipeline.
     * @var Model
     */
    public Model $queue;

    /**
     * The last object of the blueprint.
     * @var bool
     */
    public bool $last;

    public int $step;

    /**
     * @param array|object $attributes
     */
    public function __construct(
        array|object $attributes
    )
    {
        $this->fill($attributes);
    }

    /**
     * @param array|object $attributes
     * @return $this
     */
    final public function fill(
        array|object $attributes
    ): self
    {

        foreach ($attributes as $key => $val) {
            if ($key === 'id') {
                $this->step = $val;
                $this->id = $val;
            }else{
                $this->{$key} = $val;
            }
        }

        return $this;
    }
}

