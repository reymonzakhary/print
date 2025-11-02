<?php

namespace App\Blueprints;

use App\Blueprints\Contracts\BlueprintFactoryInterface;
use Illuminate\Http\Request;

class BlueprintFactory implements BlueprintFactoryInterface
{

    /**
     * @var array|string[]
     */
    protected array $uses = [
        'action' => "\\App\\Blueprints\\Actions\\",
        'transaction' => "\\App\\Blueprints\\transactions\\",
        'event' => "\\App\\Blueprints\\events\\",
    ];

    /**
     * @param Request        $request
     * @param object         $pipe
     * @param BlueprintStack $pipeline
     * @param string         $signature
     */
    public function make(
        Request        $request,
        object         $pipe,
        BlueprintStack $pipeline,
        string         $signature
    ): void
    {
        $class = optional($this->uses)[$pipe->uses] . $pipe->{$pipe->uses}->model;
        if (class_exists($class)) {
            $pipeline->add($request, new Pipeline($pipe), new $class(), $signature);
        }
    }
}
