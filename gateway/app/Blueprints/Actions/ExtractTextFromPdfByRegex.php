<?php

namespace App\Blueprints\Actions;

use App\Blueprints\Contracts\Abstracts\Action;
use App\Blueprints\Contracts\ActionContractInterface;
use Illuminate\Support\Facades\Storage;
use Spatie\PdfToText\Exceptions\PdfNotFound;
use Spatie\PdfToText\Pdf;

class ExtractTextFromPdfByRegex extends Action implements ActionContractInterface
{

    /**
     * @return mixed
     */
    public function handle(): mixed
    {
        $name = cleanName($this->from->getClientOriginalName());
        if (!Storage::disk('local')->exists("{$this->tmp_path}/{$name}")) {
            $this->from = $this->from->move(Storage::disk('local')->path($this->tmp_path), $name);
        }
        $path = Storage::disk('local')->path("{$this->tmp_path}/{$name}");
        if ($this->config) {
            $this->output = [
                'collection' => $this->{$this->config->separateBy}(),
                'path' => $path
            ];
        }
        return $this->output;
    }

    /**
     * @return array
     * @throws PdfNotFound
     */
    protected function regex(): array
    {
        preg_match_all(
            $this->config->regex->pattern,
            (new Pdf())->setPdf($this->from)->setOptions(['raw'])->text(),
            $matches
        );
        $values = explode(',', $this->config->regex->values);
        return collect($matches[$this->config->regex->key])
            ->map(fn($value, $key) => collect($values)
                ->mapWithKeys(fn($v) => [
                    (string)$v => trim($matches[$v][$key]),
                    "pageCount" => $key + 1
                ]
                )->toArray()
            )
            ->groupBy($this->config->regex->key)
            ->toArray();
    }
}
