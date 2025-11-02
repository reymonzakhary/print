<?php

namespace App\Blueprints\Actions;

use App\Blueprints\Blueprint;
use App\Blueprints\Contracts\Abstracts\Action;
use App\Blueprints\Contracts\ActionContractInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ProcessBlueprints  extends Action implements ActionContractInterface
{
    /**
     * handling multiple blueprints
     * @return mixed|void
     * @throws \Illuminate\Validation\ValidationException
     * @throws \Throwable
     */
    public function handle()
    {
        $files = $this->request->files;

        $keys = collect($this->request->all())
            ->filter(fn($rq,$k) => Str::contains($k,'Action'))->keys()->toArray();
        $request = new Request($this->request->except(...$keys));

        $request->merge([
            'signature' => Str::uuid(),
            'beside' => true,
            'child' => false,
            'user' => $this->request->user,
            'tenant' => $this->request->tenant,
            'hostname' => $this->request->hostname,
            "has_main" => true,
            "attachment_to" => $this->request->get('main_attachment_to') ??$this->request->get('attachment_to'),
            "main_attachment_to" => $this->request->get('main_attachment_to') ??$this->request->get('attachment_to'),
            "main_attachment_destination" => $this->request->get('main_attachment_destination') ?? $this->request->get('attachment_destination'),
            "main_attachment_type" => $this->request->get('attachment_type'),
        ]);

        collect($files)->each(fn($file, $key) => $request->files->set(
            $key,
            $file
        ));
        collect(optional($this->input)->blueprints??[])
            ->each(
                fn($bp) => (new Blueprint($request, session()))->init($request, $bp)->queue()->id
            );
    }
}
