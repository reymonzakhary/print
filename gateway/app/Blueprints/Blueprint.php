<?php


namespace App\Blueprints;

use App\Blueprints\Contracts\BlueprintContactInterface;
use App\Blueprints\Contracts\BlueprintFactoryInterface;
use App\Enums\QueueProcessStatus;
use App\Foundation\Status\Status;
use App\Jobs\Tenant\Blueprints\HandelBlueprintJob;
use App\Models\Tenant\Queue;
use App\Models\Tenant\User;
use Exception;
use Hyn\Tenancy\Models\Hostname;
use Hyn\Tenancy\Models\Website;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Http\Request;
use Illuminate\Session\SessionManager;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use JsonException;

class Blueprint implements BlueprintContactInterface
{
    /**
     * @var Request
     */
    protected Request $request;

    /**
     * @var SessionManager
     */
    protected SessionManager $session;

    /**
     * @var Queue|null
     */
    protected $job;

    /**
     * @var Website
     */
    protected Website $tenant;

    /**
     * @var Hostname
     */

    protected Hostname $hostname;
    /**
     * @var User
     */
    protected User $user;

    /**
     * @var BlueprintFactoryInterface|Application|mixed
     */
    protected BlueprintFactoryInterface $factory;

    /**
     * @var BlueprintStack|Application|mixed
     */
    protected BlueprintStack $pipeline;

    /**
     * @var int
     */
    protected int $start;

    /**
     * @var mixed
     */
    protected mixed $blueprint;

    /**
     * @var array|null
     */
    protected ?array $configuration = [];

    /**
     * @var int|null
     */
    protected ?int $blueprint_id = null;

    /**
     * @var null|string
     */
    protected ?string $type;

    /**
     * @var int|null
     */
    protected ?int $type_id;

    /**
     * uuid
     * @var null|string
     */
    protected ?string $signature = null;

    /**
     * @var mixed|null
     */
    protected ?Blueprint $queue = null;

    /**
     * Constructor.
     *
     * @param Request|null $request The request object.
     * @param SessionManager $session The session manager instance.
     *
     *
     */
    public function __construct(
        null|Request   $request,
        SessionManager $session
    )
    {
        $this->request = $request;
        $this->tenant = $this->request->tenant;
        $this->hostname = $this->request->hostname;
        $this->session = $session;
        $this->pipeline = app(BlueprintStack::class);
        $this->factory = app(BlueprintFactoryInterface::class);
    }

    /**
     * Initializes the blueprint object.
     *
     * @param Request|null $request The request object.
     * @param int|null $bp The blueprint ID. (optional)
     * @return Blueprint The initialized blueprint object.
     * @throws BindingResolutionException
     * @throws Exception
     */
    public function init(
        null|Request $request,
        null|int     $bp = null
    ): Blueprint
    {
        $this->request = $request ?? $this->request;
        $this->signature = $this->request->signature;
        $this->type = $this->request->get('type');
        $this->type_id = $this->request->{$this->type}?->id;
        $this->blueprint = $bp ?
            $this->request->product->blueprints->where('ns', $this->request->get('ns'))->where('id', $bp)->first() :
            $this->request->product->blueprints->where('ns', $this->request->get('ns'))->first();
        $this->blueprint_id = $this->blueprint?->id;
        try {
            $this->configuration = (bool)$this->blueprint?->configuration ?
                json_decode(
                    json_encode($this->blueprint?->configuration, JSON_THROW_ON_ERROR)
                    , false, 512, JSON_THROW_ON_ERROR) :
                [];

        } catch (JsonException $e) {
            if($this->request->get('child')) {
                throw new \RuntimeException("Not valid blueprint.", 422);
            }
            throw ValidationException::withMessages([
                'blueprint' => __('Not valid blueprint.')
            ]);
        }

        if (!$this->blueprint) {
            if($this->request->get('child')) {
                throw new \RuntimeException("Not valid blueprint.", 422);
            }
            throw ValidationException::withMessages([
                'blueprint' => __('Not a valid blueprint.')
            ]);
        }
        $this->bootedIfNeed();
        return $this;

    }

    /**
     * @param int $step
     * @return Request|void
     * @throws BindingResolutionException|\Throwable
     */
    public function runAsPreferred(
        int $step = 1
    )
    {
        if ($this->blueprint) {
            if (!$this->blueprint->pivot->queueable) {
                return $this->run($step);
            }
            $this->queue($step);
        }
    }

    /**
     * @throws BindingResolutionException
     * @throws Exception
     */
    public function run(int $step = 1): Request
    {
        $this->request->user = $this->request->user()??$this->request->user;
        if ($this->configuration) {
            $item = end($this->configuration);
            $last = $item->id;
            $end = (int)$item->id;
            $this->handleConfiguration($step, $end, $last);
            $this->pipeline->handle();
        }
        $this->job->update([
            'started' => true,
            'process' => QueueProcessStatus::FOREGROUND,
            'st' => Status::IN_PROGRESS
        ]);
        return $this->request;
    }

    /**
     * Queue the job for background processing.
     *
     * @param int $step
     * @return Queue
     * @throws BindingResolutionException
     * @throws Exception
     * @throws \Throwable
     */
    public function queue(
        int $step = 1
    ):Queue
    {
        $this->job->update([
            'started' => true,
            'process' => QueueProcessStatus::BACKGROUND,
            'st' => Status::IN_PROGRESS
        ]);

        list($files, $keys) = $this->handleFiles();

        $request = new Request($this->request->except(...$keys));
        $request->tenant = $this->tenant;
        $request->hostname = $this->hostname;
        $request->user = $this->request->user()??$this->request->user;

        Bus::batch([
            new HandelBlueprintJob(
                $files,
                $request,
                $this->configuration,
                $this->factory,
                $this->job,
                $step)
        ])->dispatch();

        return $this->job;
    }

    /**
     * @param array|object $queue
     * @param bool         $last
     * @return object
     */
    private function _setJob(
        array|object $queue,
        bool         $last = false
    ): object
    {
        $queue->job = $this->job->items()->firstOrCreate([
            'step' => $queue->id,
            'decision' => $queue->decision,
            'queueable' => $queue->queueable,
            'model' => $queue->{$queue->uses}->model
        ], [
            'payload' => $queue,
            'decision' => $queue->decision,
            'queueable' => $queue->queueable,
            'model' => $queue->{$queue->uses}->model,
            'step' => $queue->id
        ]);

        $queue->queue = $this->job;
        $queue->total = count($this->configuration);
        $queue->last = $last;


        return $queue;
    }

    /**
     * @return Queue|null
     * @throws ValidationException
     */
    protected function instance(): ?Queue
    {
        if (
            ($this->job instanceof Queue && !$this->signature) ||
            ($this->job instanceof Queue && $this->job->signature === $this->signature)
        ) {
            $this->addInstance();
            return $this->job;
        }

        if ($this->job = Queue::where(
            [
                ['signature', $this->signature ?? $this->session->get("blueprint_{$this->tenant->uuid}_signature_{$this->type}{$this->type_id}{$this->blueprint_id}")],
                ['blueprint_id', $this->blueprint_id],
                ['st' , '!=', Status::IN_PROGRESS],
                ['st' , '!=', Status::FAILED],
                ['st' , '!=', Status::DONE]
            ])->first()) {
            $this->addInstance();
            return $this->job;
        }
        $this->session->forget("blueprint_{$this->tenant->uuid}_signature_{$this->type}{$this->type_id}{$this->blueprint_id}");
        $this->create();
        $this->addInstance();
        return $this->instance();
    }

    /**
     * @return bool
     */
    protected function exists(): bool
    {
        return (bool)$this->session->has("blueprint_{$this->tenant->uuid}_signature_{$this->type}{$this->type_id}{$this->blueprint_id}");
    }

    /**
     * Create instance in not exists
     * @throws ValidationException
     */
    protected function create(): void
    {
        if ($this->configuration) {
            $this->job = Queue::create([
                'blueprint_id' => $this->blueprint_id,
                'queueable_type' => $this->type,
                'queueable_id' => $this->type_id,
                'signature' => $this->signature,
            ]);

            $this->session->put("blueprint_{$this->tenant->uuid}_signature_{$this->type}{$this->type_id}{$this->blueprint_id}", $this->signature ?? $this->job->signature);
        } else {
            if($this->request->get('child')) {
                throw new \RuntimeException("Blueprint configuration does not exist.", 422);
            }
            throw ValidationException::withMessages([
                'configuration' => __("Blueprint configuration does not exist.")
            ]);
        }

    }

    /**
     * validate signature
     * @throws ValidationException
     */
    protected function bootedIfNeed(): void
    {
        if (!$this->type) {
            if($this->request->get('child')) {
                throw new \RuntimeException('Type is required, there is not type found in the request.', 422);
            }
            throw ValidationException::withMessages([
                'type' => __('Type is required, there is not type found in the request.')
            ]);
        }

        if (!$this->type_id) {
            if($this->request->get('child')) {
                throw new \RuntimeException('Type id is required, there is not type id found in the request.', 422);
            }
            throw ValidationException::withMessages([
                'type_id' => __('Type id is required, there is not type id found in the request.')
            ]);
        }


        $this->request->tenant = $this->tenant;
        $this->request->hostname = $this->hostname;


        if (!$this->exists()) {
            $this->create();
        }

        $this->instance();

    }

    /**
     * add job id to cart
     */
    private function addInstance(): void
    {
        if($this->request->get('attachment_to') === 'cart_item') {
            $this->request->get('attachment_destination')->update([
                'variation->blueprint->queue_id' => $this->job->id,
            ]);
        }
    }

    /**
     * Handle the configuration steps.
     *
     * @param int $step The current step.
     * @param int $end The end step.
     * @param mixed $last The last step.
     *
     * @throws BindingResolutionException If there is an error in resolving a class instance.
     * @throws Exception If an exception occurs.
     */
    private function handleConfiguration(
        int $step,
        int $end,
        mixed $last
    ): void
    {
        do
        {
            $this->factory->make(
                $this->request,
                $this->_setJob(collect($this->configuration)->firstWhere('id', $end), $last === $end),
                $this->pipeline,
                $this->job?->signature
            );
            $continue = !($end === $step);
            --$end;
        }
        while ($continue);
    }

    /**
     * Handles the uploaded files.
     *
     * @return array An array containing the file paths and keys
     * @throws Exception If an exception occurs during the handling of files
     *
     * @throws BindingResolutionException If there is an error resolving the binding
     */
    private function handleFiles(): array
    {
        $files = [];
        $keys = [];
        $signature = $this->signature ?? $this->job->signature;
        collect($this->request->files)->each(function ($file, $k) use (&$files, &$keys, $signature)
        {
            if (!Storage::disk('local')->exists("{$signature}/tmp/")) {
                Storage::disk('local')->makeDirectory("{$signature}/tmp/");
            }
            $path = cleanName($file->getClientOriginalName());
            $files[$k][cleanName($file->getClientOriginalName())] = "{$signature}/tmp/{$path}";
            $keys[] = $k;

            // Check if the runner is blueprint beside
            if (!Storage::disk('local')->exists("{$signature}/tmp/{$path}"))
            {
                if($this->request->beside) {
                    $p = Str::replace('/var/www/storage/app/public/','',$file->getRealPath());
                    Storage::disk('local')->copy($p,"{$signature}/tmp/{$path}");
                }
                move_uploaded_file($file,Storage::disk('local')->path("{$signature}/tmp/{$path}"));
            }
        });
        return [$files, $keys];
    }
}
