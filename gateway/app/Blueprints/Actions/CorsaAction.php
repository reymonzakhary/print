<?php

namespace App\Blueprints\Actions;

use App\Blueprints\Contracts\Abstracts\Action;
use App\Blueprints\Contracts\ActionContractInterface;
use App\Blueprints\Validations\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;

class CorsaAction extends Action implements ActionContractInterface
{

    /**
     * @return void
     */
    public function handle(): array
    {
        /**
         * check if config map exists
         */
        if (!$this->config?->map) {
            throw new RuntimeException(__('Config map has not been configured, please configure it before running.'));
        }

        $output_path = $this->output_path . "/" . $this->request->product->{$this->config->map->output->model}->{$this->config->map->output->column} . ".csv";
        $headers = [];
        $rows = [];
        collect($this->from)->each(function ($row, $i) use (&$rows, &$headers) {
            collect($this->config->map->headers)->each(function ($header) use (&$headers, &$rows, $row, $i) {
                $headers = array_unique(array_merge($headers, [$header->header]));
                if (optional($header)->uses) {
                    $value = Validator::hasValueFromRegExp($header->value, '/\[(.*?)\]/', __CLASS__, $row);
                    $rows[$i][] = array_flip((array)$this->config->map->{$header->uses})[$value];
                } elseif (str_starts_with(optional($header)->value, '{$')) {
                    if (optional($header)->value === '{$file_name}') {
                        $rows[$i][] = Str::after(
                            collect(Storage::disk('local')->allFiles($this->output_path))->filter(fn($path) => Str::contains($path,$row[$this->ref]))->first(),
                            $this->output_path.'/'
                        );
                    } elseif (optional($header)->value === '{$document_type}') {
//                        $replace = array_change_key_case(array_flip((array)$this->config->map->matches),CASE_LOWER);
//                        $rows[$i][] = $replace[Str::lower($this->category->slug)];
                        $rows[$i][] = optional($header)->key;
                    } elseif (optional($header)->value === '{$version}') {
                        $versions = explode( '-', Validator::hasValueFromRegExp($header->key, '/\[(.*?)\]/', __CLASS__, $row));
                        $rows[$i][] = optional($versions)[2]??1;
                    } elseif (optional($header)->value === '{$faculty}') {
                        $replace = array_change_key_case(array_flip((array)$this->config->map->matches),CASE_LOWER);
                        $rows[$i][] = $replace[Str::lower($this->category->slug)];
//                        $rows[$i][] = optional($header)->value;
                    }
                } else {
                    $rows[$i][] = Validator::hasValueFromRegExp($header->value, '/\[(.*?)\]/', __CLASS__, $row);
                }
            });
        });

        Storage::disk('local')->append($output_path, collect($headers)->implode(','));

        $headers = collect($headers)->implode(',');
        $rows = collect($rows)->map(fn($row) => Storage::disk('local')->append($output_path, collect($row)->implode(',')))->toArray();

        return $this->output = ['paths' => $output_path];
    }

}
