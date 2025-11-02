<?php

namespace App\Blueprint;

use App\Http\Requests\Cart\CartStoreRequest;
use Illuminate\Support\Str;

class BluePrintNode
{
    private $children = 0;
    private array $next = [];
//    private array|BluePrintNode $actions = [];
    private $actions;
    private string $type;

    public function __construct(
        public string $name,
                      $type = null,
        private array $config = []
    )
    {
        $this->type = (Str::lower($type) == 'action') ? 'action' : 'processors';
    }

    public function add(array|BluePrintNode $data)
    {
        if ($data instanceof BluePrintNode) {
            $this->next[] = $data;
            $this->children++;
        } else {
            return collect($data)->map(function ($el) {
                $this->add($el);
            });
        }
        return $this;
    }

    public function setActions($actionName, array|BluePrintNode $approval = [], array|BluePrintNode $rejected = [])
    {
        $className = "\App\Blueprint\Actions\\" . Str::ucfirst(Str::camel($actionName)) . 'Action';
        if (class_exists($className)) {
            $this->add((new $className)->handle($this, $approval, $rejected));
        }
        return $this;
    }

    public function count(): int
    {
        return $this->children;
    }

    public function config()
    {
        return $this->config;
    }

    public function setConfig($config = [])
    {
        $this->config = array_merge($this->config, $config);
        return $this;
    }

    public function next(CartStoreRequest $request, $next = null)
    {
        $data = ($this->type === 'action') ? 'actions' : 'next';
        $next = $next ?? array_shift($this->{$data});
        if ($next instanceof BluePrintNode) {
            $prosess = "\App\Blueprint\Processors\\" . Str::ucfirst(Str::camel($next->name)) . "Processor";
            app($prosess)->handle($next, $request);
        } else {
            foreach ($next as $node) {
                app("\App\Blueprint\Processors\\" . Str::ucfirst(Str::camel($node->name)) . "Processor")->handle($node, $request);
            }
        }
        if (empty($next->next)) {
            return $request->toArray();
        } else {

            $this->next($request, $next->next);
        }
        return $request->toArray();
    }
}
