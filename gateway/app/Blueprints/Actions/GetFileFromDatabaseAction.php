<?php

namespace App\Blueprints\Actions;

use App\Blueprints\Contracts\Abstracts\Action;
use App\Blueprints\Contracts\ActionContractInterface;
use App\Blueprints\Contracts\BlueprintIncludes;
use App\Blueprints\Validations\Validator;
use App\Enums\QueueProcessStatus;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use RuntimeException;

class GetFileFromDatabaseAction extends Action implements ActionContractInterface
{
    /**
     * @return array|null
     */
    public function handle(): array|null
    {
        $this->output = [];
        /**
         * check if validated key has been sent it otherwise get default
         */
        if ($validate = optional($this->input)->validate) {
            $method = Str::before($validate, ':');
            $from = Str::after($validate, ':');
            $data = Validator::{$method}(
                request: data_get($this->request->toArray(), $from),
                column: Str::after($this->input->from, '.')
            );
        } else {
            $data = $this->from;
        }

        /**  Get select model @var $model * */
        $model = BlueprintIncludes::TENANT_MODEL_PATH->value . $this->input->model;
        collect($data)->flatMap(
            fn($row) => collect($this->input->selector)->map(
                fn($selector) => $selector->regex ?
                    $this->getQueryStringByRegex($selector, $row, $model, $this->input?->ref) :
                    $this->getQueryString($selector, $row, $model, $this->input?->ref)
            )
        );
        return $this->output;
    }


    /**
     * @param object $selector
     * @param array  $row
     * @param string $model
     * @param string $ref
     * @return void
     * @throws ValidationException
     */
    protected function getQueryStringByRegex(
        object $selector,
        array  $row,
        string $model,
        string $ref = 'value'
    ): void
    {
        $value = Str::upper(
            Validator::HasValueFromRegExp($selector->cond->expr, "/\[(.*?)]/", 'GetFileFromDatabaseAction', $row)
        );

        $this->extracted($model, $selector, $value, $row[$ref]);
    }

    /**
     * @param object $selector
     * @param array  $row
     * @param string $model
     * @param string $ref
     * @return void
     * @throws ValidationException
     */
    protected function getQueryString(
        object $selector,
        array  $row,
        string $model,
        string $ref = 'value'
    ): void
    {
        $value = Str::upper($selector->cond->expr);

        $this->extracted($model, $selector, $value, $row[$ref]);
    }

    /**
     * @param string $model
     * @param object $selector
     * @param string $value
     * @param        $row
     * @throws ValidationException
     */
    protected function extracted(string $model, object $selector, string $value, $row): void
    {
        $file = app($model)->whereRaw("UPPER({$selector->cond->column}) = ?", $value)->with('media')->first()?->media?->first();
        if (!$file) {
            if ($this->queue->process === QueueProcessStatus::BACKGROUND) {
                throw new RuntimeException("The template $value not exists.");
            }

            throw ValidationException::withMessages([
                'template' => __('The template :template not exists.', [
                    'template' => $value
                ])
            ]);
        }
        $this->output[$row] = $file;
    }


}
