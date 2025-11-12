<?php

namespace App\Blueprints\Actions;

use App\Blueprints\Contracts\Abstracts\Action;
use App\Blueprints\Contracts\ActionContractInterface;
use App\Models\Tenant\DesignProviderTemplate;
use App\Models\Tenant\Media\FileManager;
use Exception;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Smalot\PdfParser\Config;
use Smalot\PdfParser\Parser;

class GetTextPositionAction extends Action implements ActionContractInterface
{
    /**
     * @return mixed|void
     * @throws Exception
     */
    public function handle()
    {

        collect($this->from)->each(function ($v, $k) {

            $config = new Config();
            $config->setDataTmFontInfoHasToBeIncluded(true);

            if (
                $v instanceof DesignProviderTemplate ||
                $v instanceof FileManager
            ) {
                $file = cleanName("{$this->template_path}/{$k}-{$v->name}");

                if (!Storage::disk('local')->exists($file)) {
                    cloneData(
                        $v->disk,
                        $this->request->tenant->uuid . '/' . $v->path . '/' . $v->name,
                        'local',
                        $file
                    );
                }
                $v = $file;
            }
            $path = Storage::disk('local')->path($v);

            /** check if file exists or wait */
            do {
                if ($this->maximum_loop_count === 0 || Storage::disk('local')->exists($v)) {
                    break;
                }
                $this->maximum_loop_count--;
                usleep(5000);
            } while (true);

//            sleep(1);

            $parser = new Parser([], $config);
            $pdf = $parser->parseFile($path);
            $pages = $pdf->getPages();
            foreach ($this->input->config->search as $key) {

                $this->output[$k] = collect($pages)->map(fn($page, $index) => collect($page->getDataTm())
                    ->reject(fn($p) => !in_array($key, $p, true))
                    ->map(fn($p) => [
                        'ft' => $p[0][3],
                        'x' => $p[0][4],
                        'y' => $p[0][5],
                        'key' => $p[1],
                        'page' => $index + 1,
                        "path" => Str::replace('//', '/', $v),
                        "disk" => 'local'
                    ]
                    ))->reject(fn($p) => count($p->toArray()) === 0)->flatten(1)->toArray();
            }
        });

        return $this->output;
    }
}
