<?php

namespace App\Blueprint;

use Illuminate\Support\Str;

class BluePrintList
{
    public array $list = [];

    public function add(array|BluePrintNode $data)
    {
        if ($data instanceof BluePrintNode) {
            $this->list[] = $data;
            $this->nodes++;
        } else {
            $key = array_key_first($data);
            $newList = new self($key);
            collect(array_values(($data[$key])))->map(fn ($el) => $newList->add($el));
            $this->list[] = $newList;
        }

    }

    public function get(int $index)
    {
        return $this->list[$index];
    }

    public function count(): int
    {
        return $this->nodes;
    }

    public function search(string $index, ?BluePrintList $data = null, $next = null)
    {
        $pattern = explode('-', $index);
        $current = array_shift($pattern);

        $data = $data && $data->count() ? $data : $this;
        if (optional($data)[$current] && $data[$current] instanceof BluePrintNode) {
            return $data[$current];
        }
        if (($data instanceof BluePrintList) && $data->count()) {
            return $this->search(implode('-', $pattern), $data);
        }
        return null;
    }

    public function setAction($value, $config = null)
    {
        $this->action = $value;
        $this->actionConfig = $config ?? [];
    }

    public function next($request = [])
    {
        $className = "\App\Blueprint\Actions\\" . Str::ucfirst(Str::camel($this->action)) . 'Action';
        if (class_exists($className)) {


            foreach ((new $className)->handle($this) as $node) {
                app("\App\Blueprint\Processors\\" . Str::ucfirst(Str::camel($node->name)) . "Processor")->handle($node, $request);
            }
            return $request;
        }

    }
}
