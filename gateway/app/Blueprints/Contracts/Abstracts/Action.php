<?php

namespace App\Blueprints\Contracts\Abstracts;

use App\Blueprints\Contracts\Traits\HandleStatus;
use App\Blueprints\Contracts\Traits\HasNotification;
use App\Blueprints\Pipeline;
use App\Enums\QueueProcessStatus;
use App\Events\Tenant\Blueprints\FailedBlueprintRunnerEvent;
use App\Events\Tenant\Blueprints\FinishedBlueprintWorkerEvent;
use App\Events\Tenant\Blueprints\StartedBlueprintWorkerEvent;
use App\Foundation\Media\MediaType;
use App\Foundation\Status\Status;
use App\Models\Tenant\Media\FileManager;
use App\Models\Tenant\Queue;
use App\Models\Tenant\QueueItem;
use Carbon\Carbon;
use Exception;
use File;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use JsonException;

abstract class Action
{
    use HandleStatus,
        HasNotification;

    /**
     * pipeline id
     *
     * @var int
     */
    protected int $id;

    /**
     * The reusable property if enabled, give you the ability to reuse the same output from prev action if exists.
     * Default false
     * @var bool
     */
    public bool $reusable = false;

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
    public mixed $config;

    /**
     * holding the dependsOnConfig data what coming with dependsOn to override the default behavior  action or request
     * @var mixed
     */
    public mixed $dependsOnConfig;

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
    protected bool $file;

    /**
     * The limit prop to set count for output of the action.
     * @var int|null
     */
    protected ?int $limit = null;

    /**
     * The input object is imported values from parent or other to the action.
     *
     * @var object|array
     */
    protected array|object $input;

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
     * @var Queue
     */
    protected Queue $queue;

    /**
     * the queue item instance from the db.
     * @var QueueItem
     */
    protected QueueItem $job;

    /**
     * @var Request $request
     */
    protected Request $request;

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
     * @var mixed
     */
    protected mixed $dependsOn = null;

    /**
     * @var null|int
     */
    protected mixed $dependsOnId = null;

    /**
     * @var string|null
     */
    protected ?string $ref = null;

    /**
     * Defined witch pdf tool service will be used.
     * Available are pdftool | pdfco
     * @var string
     */
    protected string $tool = 'pdftool';

    /**
     * @var null|string
     */
    protected ?string $validate = null;

    /**
     * @var mixed
     */
    protected mixed $product = null;

    /**
     * @var mixed
     */
    protected mixed $category = null;

    /**
     * @param Request  $request
     * @param Pipeline $pip
     * @param callable $next
     * @param string   $signature
     * @return mixed
     * @throws ValidationException
     * @throws JsonException
     */
    public function __invoke(
        Request  $request,
        Pipeline $pip,
        callable $next,
        string   $signature
    ): mixed
    {
        $this->signature = $signature;

        $this->fill($pip, $request);

        if (!$pip->active) {
            $this->output = data_get($this->request->toArray(), $this->input->from);
            return $next($request);
        }
        $this->bootIfNecessary();

        return $next($request);
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
        $this->job = $attributes->job;
        $this->queue = $attributes->queue;
        $this->pipeline = $attributes;
        $this->last = $attributes->last;
        $this->config = optional($this->input)->config ?? json_encode([], JSON_THROW_ON_ERROR);
        $this->tool = optional($this->input)->tool ?? 'pdftool';
        $this->dependsOnConfig = optional($attributes->action)->config ?? json_encode([], JSON_THROW_ON_ERROR);
        $this->total = $attributes->total;
        $this->request = $request;
        $this->uses = optional($this->input)->uses ?? [];
        $this->dependsOnId = optional($this->input)->dependsOn ? data_get($this->request->all(), Str::before($this->input?->dependsOn, '.') . '.id') : null;
        $this->dependsOn = optional($this->input)->dependsOn ? data_get($this->request->all(), $this->input?->dependsOn) : [];
        $this->ref = optional($this->input)->ref;
        $this->validate = optional($this->input)->validate;
        $this->tmp_path = "{$this->signature}/tmp";
        $this->tmp_output_dir = "{$this->signature}/output_tmp/{$this->request->product->slug}";
        $this->assets_path = "{$this->signature}/assets";
        $this->assets_tmp_path = "{$this->signature}/assets/tmp";
        $this->template_path = "{$this->signature}/template";
        $this->output_path = "{$this->signature}/output/{$this->request->product->slug}/{$this->request->get('override_path')}";
        $this->output_dir = "{$this->signature}/output";
        $this->product = $this->request->product;
        $this->category = $this->request->product->category;

        $this->bootStorage();

    }

    /**
     * handle the booting action
     * @throws ValidationException
     */
    protected function bootIfNecessary(): void
    {
        $this->bootBlueprint();

        $this->waitIfNecessary();

        $this->job->update([
            'start_at' => Carbon::now(),
            'attempts' => ++$this->job->attempts,
            'payload' => $this->job->payload,
            'busy' => true,
        ]);

        $this->pullFromRequestOrAction();

        $this->notifyProgress();

        try {

            $start = microtime(true);
            if(!$this->reuseIfNeeded()) {
                $this->handle();
            }

        } catch (Exception $e) {
            Storage::disk('local')->deleteDirectory($this->output_path);
            if ($this->queue->process === QueueProcessStatus::BACKGROUND) {
                event(new FailedBlueprintRunnerEvent($this->queue, $this->job->model, $this->id, $e->getMessage(), $this->request->get('attachment_destination')));
                if ($this->request->get('attachment_to') !== 'self') {
//                    if ($this->job->attempts >= 2 && !$this->request->get('child')) {
                    if (!$this->request->get('child')) {
                        $this->request->get('has_main') ?
                            $this->request->get('main_attachment_destination')->update([
                                'st' => Status::FAILED
                            ]):
                        $this->request->get('attachment_destination')
                            ->update([
                                'st' => Status::FAILED
                            ]);
                    }
                }
                throw new \RuntimeException("On {$this->job->model} {$e->getMessage()}, Line {$e->getLine()}, In file {$e->getFile()} :{$this->job->id} ");

            }

            throw ValidationException::withMessages([
                $this->job->model => [
                    $e->getMessage(),
                    $e->getLine(),
                    $e->getFile()
                ]
            ]);
        }

        $this->mergeRequest();

        if (!$this->job->queueable) {
            $this->job->update([
                'end_at' => Carbon::now(),
                'await' => false,
                'busy' => false,
            ]);
        }

        $duration = microtime(true) - $start;

        \Log::debug(['product' => $this->product->name, 'id' => $this->id, 'job' =>  $this->job->model, 'tijd' => $duration]);

        $this->leaveIfFinished();

    }

    /**
     * Handel if reusable enabled to get data from other pipeline.
     * @return bool
     */
    protected function reuseIfNeeded(): bool
    {
        if ($this->reusable && !$this->file && array_key_exists($this->job->model, $this->request->all()) && data_get($this->request->all(), $this->model)) {
            $model = data_get($this->request->all(),$this->model);
            $keys = array_keys($model);
            $k = last(array_filter($keys, static fn($key) => preg_match("/^(?!\d)/", $key), true));
            $this->output = $model[$k];
            return true;
        }
        return false;
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
     * merge to request
     */
    protected function mergeRequest(): void
    {
        $this->request->merge([
            $this->model => array_merge([
                "{$this->job->step}_id" => $this->job->step,
                $this->as => $this->output
            ],
                $this->request->{$this->model} ?? []
            )
        ]);
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
     * hold the request until the last action is finished
     */
    protected function waitIfNecessary(): void
    {
        if ($this->waitForDepended() || ($this->parent()?->queueable && $this->parent()?->busy)) {
            $this->job->update([
                'await' => true,
            ]);
            /** wait for parent  */
            do {
                if (!$this->parent()?->busy && !$this->depended()?->busy) {
                    break;
                }
                usleep(8000);
            } while (true);
            /** wait for parent finished */
            $this->job->update([
                'await' => false,
            ]);
        }
    }

    /**
     * @return bool
     */
    protected function waitForDepended(): bool
    {
        return (bool)$this->depended()?->queueable && (bool)$this->depended()?->busy;
    }

    /**
     * @return Model|null
     */
    protected function depended(): Model|null
    {
        return $this->queue->items()->where('step', $this->dependsOnId)->first();
    }

    /**
     * @param int|null $id
     * @return Model|null
     */
    protected function parent(?int $id = null): Model|null
    {
        return $this->queue->items()
            ->where(
                'step',
                $id ?? $this->id !== 1 ? $this->id - 1 : null
            )->first();
    }

    /**
     * Update the main blueprint instance.
     * @return void
     */
    protected function bootBlueprint(): void
    {
        if ($this->id === 1 && !$this->queue->busy) {
            $this->queue->update([
                'busy' => true,
            ]);

            if (!$this->request->get('child')) {
                $this->bootStatus();
                event(new StartedBlueprintWorkerEvent($this->request->get('attachment_destination')));
            }
        }
    }

    /**
     * finished processing
     */
    protected function leaveIfFinished(): void
    {
        if ($this->last && $this->queue->busy) {
            $this->queue->update([
                'busy' => false,
                'started' => false,
                'st' => Status::DONE
            ]);
            Session::forget("blueprint_{$this->request->tenant->uuid}_signature_{$this->queue->queueable_type}{$this->queue->queueable_id}{$this->queue->blueprint_id}");

            $this->setAttachmentTo();

            if (!$this->request->get('child')) {
                $this->bootStatus();

                event(new FinishedBlueprintWorkerEvent($this->request->get('attachment_destination'), $this->output));
            }
        }
    }

    /**
     * handle response destinations
     */
    protected function setAttachmentTo(): void
    {
        match ($this->request->get('attachment_to')) {
            'self' => $this->addMediaToRequest(),
            'cart_item' => $this->addMediaToItem(),
            'order_item' => $this->addMediaToOrderItem(),
            null => ''
        };

        $this->addOutputToQueue();
    }

    /**
     * @return bool
     */
    protected function addOutputToQueue(): bool
    {
        return $this->queue->update([
            'output' => $this->request->all()
        ]);
    }

    protected function addMediaToOrderItem()
    {
        $this->attachOutput();
        // remove local directory clean up
        $this->cleanUpDirectory();
    }

    /**
     *
     */
    protected function addMediaToItem(): void
    {
        match ($this->output['disk']) {
            'carts' => $this->fromCartsDisk(),
            'local' => $this->fromLocalDisk()
        };

        if (!$this->request->get('child')) {
            Session::forget("blueprint_{$this->request->tenant->uuid}_signature_{$this->queue->queueable_type}{$this->queue->queueable_id}{$this->queue->blueprint_id}");
            Storage::disk('local')->deleteDirectory($this->output_dir);
            Storage::disk('local')->deleteDirectory($this->tmp_output_dir);
        }
    }

    /**
     *
     */
    protected function addMediaToRequest(): void
    {
        $this->{$this->request->get('attachment_destination')}->merge([
            'output' => $this->output
        ]);

        if (!$this->request->get('child')) {
            Session::forget("blueprint_{$this->request->tenant->uuid}_signature_{$this->queue->queueable_type}{$this->queue->queueable_id}{$this->queue->blueprint_id}");
            Storage::disk('local')->deleteDirectory($this->output_dir);
            Storage::disk('local')->deleteDirectory($this->tmp_output_dir);
        }
    }

    /**
     *
     */
    private function fromCartsDisk(): void
    {
        $this->attachOutput();
    }

    private function fromLocalDisk(): void
    {
        $this->attachOutput();
    }

    /**
     * @return void
     */
    private function attachOutput(): void
    {
        $fileName = pathinfo($this->output['storage_path'], PATHINFO_BASENAME);
        $path = rtrim(Str::before($this->output['storage_path'], $fileName), '/');
        $media = new FileManager();
        $media->user_id = $this->request->user?->id;
        $media->name = $fileName;
        $media->disk = $this->output['storage_disk'];
        $media->collection = 'CartVariation';
        $media->type = File::mimeType(Storage::disk('local')->path($this->output['path']));
        $media->size = Storage::disk('local')->size($this->output['path']);
        $media->group = MediaType::getGroupType($media->type);
        $media->path = $path;
        $media->ext = File::extension($fileName);
        $media->save();
        optional($this->request)->get('has_main') ?
            $this->request->get('main_attachment_destination')->media()->save($media, [
                'user_id' => $this->request->user?->id,
                'uuid' => Str::uuid(),
                'collection' => 'CartVariation',
                'size' => $media->size,
            ]):
            $this->request->get('attachment_destination')->media()->save($media, [
                'user_id' => $this->request->user?->id,
                'uuid' => Str::uuid(),
                'collection' => 'CartVariation',
                'size' => $media->size,
            ]);
    }

    /**
     * return void
     */
    private function bootStorage(): void
    {
        /**
         * tmp path
         */
        if (!Storage::disk('local')->exists($this->tmp_path)) {
            Storage::disk('local')->makeDirectory($this->tmp_path);
        }

        /**
         * tmp output dir
         */
        if (!Storage::disk('local')->exists($this->tmp_output_dir)) {
            Storage::disk('local')->makeDirectory($this->tmp_output_dir);
        }

        /**
         * template path
         */
        if (!Storage::disk('local')->exists($this->template_path)) {
            Storage::disk('local')->makeDirectory($this->template_path);
        }

        /**
         * assets path
         */
        if (!Storage::disk('local')->exists($this->assets_path)) {
            Storage::disk('local')->makeDirectory($this->assets_path);
        }

        /**
         * assets path
         */
        if (!Storage::disk('local')->exists($this->assets_tmp_path)) {
            Storage::disk('local')->makeDirectory($this->assets_tmp_path);
        }

        /**
         * output path
         */
        if (!Storage::disk('local')->exists($this->output_path)) {
            Storage::disk('local')->makeDirectory($this->output_path);
        }
    }

    /**
     * remove all generated folders and files
     */
    protected function cleanUpDirectory(): void
    {
        Storage::disk('local')->deleteDirectory($this->output_path);
        Storage::disk('local')->deleteDirectory($this->assets_path);
        Storage::disk('local')->deleteDirectory($this->template_path);
        Storage::disk('local')->deleteDirectory($this->tmp_output_dir);
    }

    /**
     * @return mixed
     */
    abstract public function handle();
}
