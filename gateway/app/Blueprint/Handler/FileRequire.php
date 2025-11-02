<?php

namespace App\Blueprint\Handler;

use App\Blueprint\Validators\ValidateFileRequest;
use App\Blueprint\Validators\ValidateModelRequest;
use Illuminate\Validation\ValidationException;

class FileRequire
{
    public array $files;

    /**
     * @param array $files
     * @return FileRequire
     */
    public function handle(
        array $files
    ): FileRequire
    {
        $this->files = $files;
        $this->build();
        return $this;
    }

    protected function build(): void
    {
        $files = collect($this->files)->map(function ($file, $key) {
            return $this->render($key, ...$file);
        })->values()->toArray();
        $this->files = array_reduce($files, 'array_merge', []);
    }

    /**
     * @param string $key
     * @param string $from
     * @param string $mode
     * @param string $type
     * @param string $model
     * @param array  $lookup
     * @param array  $selector
     * @param array  $validate
     * @return array
     * @throws ValidationException
     */
    protected function render(
        string $key,
        string $from,
        string $mode,
        string $type,
        string $model,
        array  $lookup = [],
        array  $selector = [],
        array  $validate = []
    ): array
    {
        return match ($type) {
            'request' => (new ValidateFileRequest($mode(), $validate, $key, $from, $model, $selector, $lookup))->render(),
            'model' => (new ValidateModelRequest($mode, $type, $validate, $key, $from, $model, $selector, $lookup))->render()
        };
    }

    /**
     * @return array
     */
    public function run(): array
    {
        return $this->files;
    }
}
