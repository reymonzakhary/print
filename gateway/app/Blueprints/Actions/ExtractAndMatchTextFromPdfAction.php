<?php

namespace App\Blueprints\Actions;

use App\Blueprints\Contracts\Abstracts\Action;
use App\Blueprints\Contracts\ActionContractInterface;
use App\Blueprints\Processors\MatchStringWithReplace;
use App\Models\Tenant\DesignProviderTemplate;
use App\Models\Tenant\Media\FileManager;
use App\Models\Tenant\Media\FileManager as FileManagerMedia;
use Exception;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Smalot\PdfParser\Parser;
use Spatie\PdfToText\Exceptions\PdfNotFound;
use Spatie\PdfToText\Pdf;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class ExtractAndMatchTextFromPdfAction extends Action implements ActionContractInterface
{
    /**
     * @return array|mixed|null
     * @throws PdfNotFound
     */
    public function handle()
    {
        $this->output = [];

        $this->from = is_array($this->from) ? $this->from : [$this->from];

        collect($this->from)->each(function ($template, $key) {
            $absolute_file_path = null;
            $file = null;
            /** check if  instanceof file manager */
            if (
                $template instanceof FileManager ||
                $template instanceof DesignProviderTemplate ||
                $template instanceof FileManagerMedia
            ) {
                $file = cleanName("{$this->template_path}/{$key}-{$template->name}");
                $absolute_file_path = Storage::disk('local')->path($file);
                if (!Storage::disk('local')->exists($file)) {
                    cloneData(
                        $template->disk,
                        $this->request->tenant->uuid . '/' . $template->path . '/' . $template->name,
                        'local',
                        $file
                    );
                }
            }

            if ($template instanceof UploadedFile) {
                $file = cleanName("{$this->template_path}/{$template->getClientOriginalName()}");
                if (!Storage::disk('local')->exists($file)) {
                    Storage::disk('local')->put($file, $template);
                }
                $absolute_file_path = $template;
            }

            /** if instanceof string */
            if (is_string($template)) {
                $file = $template;
                $absolute_file_path = Storage::disk('local')->path($template);
            }
            $row = collect(...array_map(fn($c) => data_get($this->request->all(), $c), $this->input->uses))->firstWhere($this->input->ref, $key);

            if (!is_array($row)) {
                $row = optional($this->dependsOn)[$key];
                $key = optional($row)[$this->input->ref];
            }
            if (!$row) {
                throw new RuntimeException(__("No pdf found for key {$key}"));
            }
            /** check if file exists or wait */
            do {
                if (
                    is_readable($absolute_file_path) &&
                    (bool) file_get_contents($absolute_file_path)

                ) {
                    $data = file_get_contents($absolute_file_path);
                    if(false !== ($tripos = strpos(file_get_contents($absolute_file_path), '%PDF-'))) {
                        $pdfData =  $tripos > 0 ? substr($data, $tripos) : $data;
                        $starterPreg = preg_match(
                            '/[\r\n]startxref[\s]*[\r\n]+([\d]+)[\s]*[\r\n]+%%EOF/i',
                            $pdfData,
                            $matches,
                            \PREG_OFFSET_CAPTURE,
                            0
                        );

                        if($starterPreg === 1) {
                            break;
                        }
                    }
                }
                usleep(5000);
            } while (true);

            $text = $this->getTextFromTemplate($absolute_file_path);

            $toReplace = match ($this->input?->with) {
                "inLine" => match ($this->input?->inLine) {
                    true => MatchStringWithReplace::inlineStrings('/.*\[.*].*/i', $text, $row),
                    false => MatchStringWithReplace::single('/\[(.*?)\]/i', $text, $row)
                },
                "replacer" => MatchStringWithReplace::replacer($this->input->replacer, $text, $row),
                default => throw new \RuntimeException("With key is required on action ExtractAndMatchTextFromPdfAction, pipeline id {$this->id}")
            };

            if (optional($this->input)->cond) {
                //$this->input->cond->from
                $in = $this->input->cond?->if->in;
                $then = $this->input->cond?->if->then;
                $k = collect($in)->last();
                $value = collect($in)->first();
                $index = collect($toReplace[$k])->search(fn($v) => $v === $value);
                !$index ?: $toReplace[$k][$index] = $then;
            }

            $toReplace['url'] = asset(Storage::disk('local')->url($file));

            if (env('APP_ENV') === 'local') {
                if (!Storage::disk('carts')->exists($file)) {
                    cloneData(
                        'local', $file,
                        'carts', $file
                    );
                }

                $toReplace['url'] = Storage::disk('carts')->url($file);
            }
            $this->output[$key] = [
                'output_path' => $this->output_path,
                'to_replace' => $toReplace,
                'template_path' => $this->template_path
            ];
        });

        return $this->output;
    }

    /**
     * @param string $absolute_file_path
     * @return string|null'.
     */
    protected function getTextFromTemplate(
        string $absolute_file_path
    ): ?string
    {
        try {
//            $pdf = new Parser();
//            return $pdf->parseFile($absolute_file_path)->getText();
            return (new Pdf())->setPdf($absolute_file_path)->setOptions(['raw'])->text();
        } catch (Exception $e) {
            throw new RuntimeException($e->getMessage());
        }
    }

}
