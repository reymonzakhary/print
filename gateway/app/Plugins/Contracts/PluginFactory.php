<?php

namespace App\Plugins\Contracts;

use App\Plugins\Concrete\PluginFactoryInterface;
use App\Plugins\Config\PluginConfigRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PluginFactory implements PluginFactoryInterface
{
    /**
     * @var array|string[]
     */
    protected array $uses = [
        'action' => "\\App\\Plugins\\Actions\\",
        'transaction' => "\\App\\Plugins\\Transactions\\",
        'event' => "\\App\\Plugins\\Events\\",
        'hooks' => "\\App\\Plugins\\Webhooks\\",
    ];

    /**
     * @param Request $request
     * @param object $pipe
     * @param PluginStack $pipeline
     * @param string $signature
     * @param PluginConfigRepository $configRepository
     */
    public function make(
        Request     $request,
        object      $pipe,
        PluginStack $pipeline,
        string      $signature,
        PluginConfigRepository  $configRepository,
    ): void
    {
        $original_namespace = trim(optional($this->uses)[$pipe->uses], '\\');
        $model_path = trim(Str::replace('/', '\\', Str::ucfirst($pipe->{$pipe->uses}->path)), '\\');
        $model = trim($pipe->{$pipe->uses}->model, '\\');
        $class = $original_namespace . '\\' . $model_path . '\\' . $model;
        if (class_exists($class)) {
            $pipeline->add($request, new PluginPipeline($pipe), new $class(), $signature, $configRepository);
        }
    }
}
