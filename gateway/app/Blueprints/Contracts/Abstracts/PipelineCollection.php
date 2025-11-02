<?php

namespace App\Blueprints\Contracts\Abstracts;

class PipelineCollection
{

    public function collect(
        $items
    )
    {
        new static($items);

        if (!$this->items) {
            return new static($items);
        }
        return $this;
    }

    public function current()
    {
        dd(__METHOD__);
        // TODO: Implement current() method.
    }

    public function next()
    {
        dd(__METHOD__);

        // TODO: Implement next() method.
    }

    public function key()
    {
        dd(__METHOD__);

        // TODO: Implement key() method.
    }

    public function valid()
    {
        dd(__METHOD__);

        // TODO: Implement valid() method.
    }

    public function rewind()
    {
        dd(__METHOD__);

        // TODO: Implement rewind() method.
    }
}
