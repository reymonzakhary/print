<?php

namespace App\Jobs\Tenant\Blueprints;

use App\Blueprints\BlueprintStack;
use App\Foundation\Status\Status;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class HandelBlueprintJob implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(
        public $files,
        public $request,
        public $blueprint,
        public $factory,
        public $pipeline,
        public $step
    )
    {}

    /**
     * Execute the job.
     *
     * @return string
     */
    public function handle()
    {
        collect($this->files)->each(fn($paths, $k) => collect($paths)
            ->each(function($path, $key) use ($k) {
                if(Storage::disk('local')->exists($path)) {
                    $this->request->files->set(
                        $k,
                        new UploadedFile(
                            Storage::disk('local')->path($path),
                            $key,
                            \File::mimeType(Storage::disk('local')->path($path))
                        )
                    );
                }
            })
        );

        if($this->blueprint) {
            $pipeline = app(BlueprintStack::class);
            $item=end($this->blueprint);
            $last = $item->id;
            $end = (int) $item->id;
            do {

                $this->factory->make(
                    $this->request,
                    $this->_setJob($this->pipeline,collect($this->blueprint)->firstWhere('id',$end), $last===$end),
                    $pipeline,
                    $this->pipeline?->signature
                );

                $continue = !($end === $this->step);
                --$end;
            } while ($continue);
            /**
             * Run actions
             */
            try {
                $pipeline->handle($this->request);
            } catch ( \RuntimeException $e ) {
//                preg_match('/:+\d+/', $e->getMessage(), $matches);
//                if (optional($matches)[0]) {
//                    if(
//                        $this->pipeline
//                            ->items()
//                            ->where('id', (int)Str::replace(':','',optional($matches)[0]))
//                            ->first()
//                    ) {
//                        $this->delete();
//
//                        $this->pipeline->update([
//                            'st' => Status::FAILED,
//                            'busy' => false
//                        ]);
//
//                        if ($this->request->get('attachment_to') !== 'self') {
//                            $this->request->get('attachment_destination')
//                                ->update([
//                                    'st' => Status::FAILED
//                                ]);
//                        }

//                    }
//                }

                $this->delete();

                collect($this->pipeline->where('signature', $this->pipeline->signature)->get())->each(fn($q) =>
                    $q->update([
                        'st' => Status::FAILED,
                        'busy' => false,
                        'started' => false
                    ])
                );


                if ($this->request->get('attachment_to') !== 'self') {
                    $this->request->get('has_main') ?
                    $this->request->get('main_attachment_destination')->update([
                        'st' => Status::FAILED
                    ]) :
                    $this->request->get('attachment_destination')
                        ->update([
                            'st' => Status::FAILED
                        ]);
                }

                \Log::debug([
                    'error queue' => $e->getMessage(),
                    'child' => $this->request->product->name,
                    'step' => $this->step,
                ]);
                throw new \RuntimeException("{$e->getMessage()}, Line {$e->getLine()}, In file {$e->getFile()}");
            }
        }

//        dd($this->job->getJobId());
    }

    /**
     * @param              $job
     * @param array|object $queue
     * @param bool         $last
     * @return object
     */
    private function _setJob(
        $job,
        array|object $queue,
        bool $last = false
    ): object
    {
        $queue->job = $job->items()->firstOrCreate([
            'step' => $queue->id,
            'decision' => $queue->decision,
            'queueable' => $queue->queueable,
            'model' => $queue->{$queue->uses}->model,
        ],[
            'payload' => $queue,
            'decision' => $queue->decision,
            'queueable' => $queue->queueable,
            'step' => $queue->id,
            'model' => $queue->{$queue->uses}->model,
        ]);
        $queue->queue = $job;
        $queue->last = $last;
        $queue->total = count($this->blueprint);

        return $queue;
    }
}
