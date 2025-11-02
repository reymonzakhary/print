<?php

namespace App\Blueprints\Actions;

use App\Blueprints\Contracts\Abstracts\Action;
use App\Blueprints\Contracts\ActionContractInterface;
use App\Blueprints\Processors\Rotate;
use App\Blueprints\Validations\Validator;
use Illuminate\Support\Facades\Storage;
use setasign\Fpdi\PdfParser\CrossReference\CrossReferenceException;
use setasign\Fpdi\PdfParser\Filter\FilterException;
use setasign\Fpdi\PdfParser\PdfParserException;
use setasign\Fpdi\PdfParser\Type\PdfTypeException;
use setasign\Fpdi\PdfReader\PdfReaderException;

class AddTextToPdfAction extends Action implements ActionContractInterface
{
    /**
     * @return void
     * @throws CrossReferenceException
     * @throws FilterException
     * @throws PdfParserException
     * @throws PdfTypeException
     * @throws PdfReaderException
     */
    public function handle(): void
    {

        $this->output = [];

        collect($this->from)->each(function ($v, $k) {
            /** check if file exists or wait */

            /** check if file exists or wait */
            $absolute_file_path = Storage::disk('local')->path($v);
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


            $pdf = new Rotate();
            $pageCount = $pdf->setSourceFile(Storage::disk('local')->path($v));
            $text = Validator::HasValueFromRegExp($this->input->config->text, "/\[(.*?)]/", 'AddTextToPdfAction',
                collect($this->dependsOn)->filter(fn($r) => (int)$r[$this->ref] === $k)->first()
            );

            for ($i = 1; $i <= $pageCount; $i++) {
                $pdf->AddPage('p', 'A4');
                $temp = $pdf->importPage($i);
                $size = $pdf->getTemplateSize($temp);
                $pdf->useImportedPage($temp, 0, 0, $size['width'], $size['height'], true);
                $pdf->SetFont(...$this->input->config->font);
                $pdf->SetTextColor(...$this->input->config->color);
                if(optional($this->input)->page) {
                    $pdf->rotatedText(
                        $this->input->config->x,
                        $this->input->config->y,
                        $this->input?->page === $i?
                            Validator::HasValueFromRegExp($text, "/{(.*?)}/", 'AddTextToPdfAction', ['page' => $i]):
                            '',
                        $this->input->config->angle ?? 0
                    );
                }else {
                    $pdf->rotatedText(
                        $this->input->config->x,
                        $this->input->config->y,
                        Validator::HasValueFromRegExp($text, "/{(.*?)}/", 'AddTextToPdfAction', ['page' => $i]),
                        $this->input->config->angle ?? 0
                    );
                }

                $pdf->SetFontSize(1);
            }
            $pdf->Output('F', Storage::disk('local')->path($v));
            $this->output[$k] = $v;
//            $this->output['path'] = $v;
        });
    }
}
