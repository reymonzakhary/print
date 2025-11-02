<?php

namespace App\Blueprints\Actions;

use App\Blueprints\Contracts\Abstracts\Action;
use App\Blueprints\Contracts\ActionContractInterface;
use App\Blueprints\Processors\Rotate;
use App\Blueprints\Validations\Validator;
use App\Models\Tenants\DesignProviderTemplate;
use App\Models\Tenants\Media\FileManager;
use Illuminate\Support\Facades\Storage;
use Smalot\PdfParser\Config;
use Smalot\PdfParser\Parser;

class AddTextOnPositionToPdfAction extends Action implements ActionContractInterface
{

    /**
     * @TODO  DONT USE THIS in development
     * @return mixed|void
     */
    public function handle()
    {
        $config = new Config();
        $config->setDataTmFontInfoHasToBeIncluded(true);


        /**
         * Get from parent action
         */

        /**
         * Needed to run
         * needs files paths
         */
        collect($this->from)->map(function ($v, $k) use ($config) {
            // check the file if exists in local otherwise download hem
            $path = '';
            /**
             * Check if file path is string
             */
            if (is_string($v)) {
                $path = $v;
            }

            /**
             * Check if file is instance of model.
             */
            if (
                $v instanceof DesignProviderTemplate ||
                $v instanceof FileManager
            ) {
                $path = cleanName("{$this->template_path}/{$k}-{$v->name}");
            }

            /**
             * Check if file exists local otherwise download file.
             */
            if (!Storage::disk('local')->exists($path)) {
                cloneData(
                    $v->disk,
                    $this->request->tenant->uuid . '/' . $v->path . '/' . $v->name,
                    'local',
                    $path
                );
            }

            /** check if file exists or wait */
            do {
                if (
                    $this->maximum_loop_count === 0 || Storage::disk('local')->exists($path)
                ) {
                    break;
                }
                $this->maximum_loop_count--;
                usleep(5000);
            } while (true);

            $path = Storage::disk('local')->path($path);

            $parser = new Parser([], $config);
            $pdf = $parser->parseFile($path);
            //            $array = $pdf->getFonts();
            //            $font = reset($array);
            //            dd(array_keys($array));
            //            die();
            $pages = $pdf->getPages();

            foreach ($this->input->config->search as $key => $value) {

                collect($pages)->map(
                    fn ($page, $index) => collect($page->getDataTm())
                        ->reject(fn ($p) => !in_array($value, $p, true))
                        ->map(function ($p) use ($path, $k, $key) {
                            $pdf = new Rotate();
                            $pageCount = $pdf->setSourceFile($path);
                            $conf = $this->input->config->replace[$key];
                            $text = Validator::HasValueFromRegExp(
                                $conf->text,
                                "/\[(.*?)]/",
                                'AddTextOnPositionToPdfAction',
                                collect($this->dependsOn)->filter(fn ($r) => (int)$r[$this->ref] === $k)->first()
                            );

                            for ($i = 1; $i <= $pageCount; $i++) {

                                $pdf->AddPage('p', 'A4');
                                $temp = $pdf->importPage($i);
                                $size = $pdf->getTemplateSize($temp);
                                $pdf->useImportedPage($temp, 0, 0, $size['width'], $size['height'], true);

                                if ($i === $conf->page) {
                                    $pdf->SetFont(...$conf->font);
                                    $pdf->SetTextColor(...$conf->color);
                                    $pdf->rotatedText(
                                        $p[0][4] * 0.352777778,
                                        $p[0][5] * 0.352777778 + 10,
                                        Validator::HasValueFromRegExp($text, "/{(.*?)}/", 'AddTextToPdfAction', ['page' => $i]),
                                        $conf->angle ?? 0
                                    );
                                    $pdf->SetFontSize(1);
                                }
                            }
                            $pdf->Output('F', $path);
                            $pdf->close();
                        })
                )->reject(fn ($p) => count($p->toArray()) === 0)->flatten(1)->toArray();
            }
        });


        /**
         * Expected output
         *
         */
    }
}
