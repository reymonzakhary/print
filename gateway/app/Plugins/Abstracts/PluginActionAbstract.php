<?php

namespace App\Plugins\Abstracts;

use App\Plugins\Config\PluginConfigRepository;
use App\Plugins\Contracts\PluginPipeline;
use App\Utilities\Traits\ConsumesExternalServices;
use Exception;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Exception\JsonException;
use Symfony\Component\HttpFoundation\Request;

abstract class PluginActionAbstract
{
    use ConsumesExternalServices;
    /**
     * pipeline id
     *
     * @var int
     */
    protected int $id;

    /**
     * the default output path
     * @var string
     */
    protected string $output_path;

    /**
     * the default output directory
     * @var string
     */
    protected string $output_dir;

    /**
     * the default tmp path for uploaded files.
     * @var string
     */
    protected string $tmp_path;

    /**
     * the default tmp path for uploaded files.
     * @var string
     */
    protected string $tmp_output_dir;

    /**
     * the default template path
     * @var string
     */
    protected string $template_path;

    /**
     * the default assets path
     * @var string
     */
    protected string $assets_path;

    /**
     * the default assets tmp path
     * @var string
     */
    protected string $assets_tmp_path;

    /**
     * @var array
     */
    protected array $runners = [];

    /**
     * The assets hold a list of assets from storage.
     * @var array|null
     */
    protected ?array $assets = [];

    /**
     * pipeline total
     *
     * @var int|null
     */
    protected ?int $total = 0;

    /**
     * The maximum loop count is the number of loops what the check can make before forcing the loop to stop.
     * The default count will be 10 times.
     * @var int
     */
    protected int $maximum_loop_count = 120;

    /**
     * @var object
     */
    protected object $pipeline;

    /**
     * @var array|null
     */
    protected ?array $replacer;

    /**
     * holding the data what coming from previous action or request
     * @var mixed
     */
    public mixed $from;

    /**
     * holding the config data what coming with from to override the default behavior  action or request
     * @var mixed
     */
    public mixed $configRepository;

    /**
     * The response key name for the action.
     *
     * @var string
     */
    protected string $as;

    /**
     * The file prop to check if this action expected file from request or parent action.
     * @var bool
     */
    protected bool $file = false;

    /**
     * The limit prop to set count for output of the action.
     * @var int|null
     */
    protected ?int $limit = null;

    /**
     * The uses object is imported values from parent or other to the action.
     *
     * @var object|array
     */
    protected array|object $uses = [];

    /**
     * The input object is imported values from parent or other to the action.
     *
     * @var string
     */
    protected string $model;

    /**
     * The last pipe in the blueprint.
     * @var bool
     */
    protected bool $last = false;

    /**
     * the queue is instance of the main pipeline class from db.
     * @var mixed
     */
    protected mixed $queue;

    /**
     * the queue item instance from the db.
     * @var mixed
     */
    protected mixed $job;

    /**
     * @var \Illuminate\Http\Request|Request $request
     */
    protected \Illuminate\Http\Request|Request $request;

    /**
     * The output of every action.
     * @var mixed
     */
    protected mixed $output = null;

    /**
     * @var string
     */
    protected string $signature;

    /**
     * @var null|string
     */
    protected ?string $validate = null;

    /**
     * The input object is imported values from parent or other to the action.
     *
     * @var object|array
     */
    protected array|object $input;

    /**
     *
     * @var string|null $base_uri The base URI of the Laravel application
     */
    protected ?string $base_uri;

    /**
     * @var int
     */
    protected int $step;

    /**
     * Handle the request invocation
     * @param Request $request
     * @param PluginPipeline $pip
     * @param callable $next
     * @param string $signature
     * @param PluginConfigRepository $configRepository
     * @return mixed
     * @throws ValidationException
     */
    public function __invoke(
        Request  $request,
        PluginPipeline $pip,
        callable $next,
        string   $signature,
        PluginConfigRepository  $configRepository
    ): mixed
    {
        $this->base_uri = $configRepository->getBaseUri();
        $this->signature = $signature;
        $this->configRepository = $configRepository;
        $this->fill($pip, $request);
        $this->bootIfNecessary();

        return $next($request);
    }

    /**
     * Handle the necessary boot process.
     *
     * @return void
     * @throws ValidationException
     */
    protected function bootIfNecessary(): void
    {
        $this->pullFromRequestOrAction();

        try {
            $this->handle();
        } catch (Exception $e) {
            throw ValidationException::withMessages([
                $this->model => [
                    $e->getMessage(),
                    $e->getLine(),
                    $e->getFile()
                ]
            ]);
        }
        $this->mergeRequest();
    }

    /**
     * pull file from request if exists
     */
    protected function pullFromRequestOrAction(): void
    {
        if ($this->file) {
            $this->output = $this->getFileByType($this->input, $this->request);
        } else {
            $this->from = data_get($this->request->toArray(), $this->input->from);
        }
    }

    /**
     * @param object $input
     * @param        $request
     * @return mixed
     */
    protected function getFileByType(
        object $input,
               $request
    ): mixed
    {
        return collect($request->files)->filter(fn($f) => $f->getClientMimeType() === $input->from)->first();
    }

    /**
     * fill all the class properties
     * @param $attributes
     * @param $request
     * @throws JsonException
     */
    protected function fill(
        $attributes,
        $request
    ): void
    {
        foreach ($attributes->action as $key => $val) {
            $this->{$key} = $val;
        }
        $this->id = $attributes->id;
        $this->pipeline = $attributes;
        $this->last = $attributes->last;
        $this->step = $attributes->step;
        $this->request = $request;
    }

    /**
     * Merge specific data into the request object
     */
    protected function mergeRequest(): void
    {
        $this->request->merge([
            $this->model => array_merge([
                "{$this->step}_id" => $this->step,
                $this->as => $this->output
            ],
                $this->request->{$this->model} ?? []
            )
        ]);
    }

    /**
     * Handle the specific action in a concrete implementation
     */
    abstract public function handle();
}
