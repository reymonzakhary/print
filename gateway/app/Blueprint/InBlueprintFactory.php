<?php

namespace App\Blueprint;

class InBlueprintFactory
{

    protected mixed $from;

    protected mixed $mode;

    protected mixed $selector;

    protected mixed $files;

    protected mixed $results;

    /**
     * @param string $from
     * @param mixed  $mode
     * @param mixed  $files
     * @param mixed  $selector
     * @return array
     */
    public function run(
        string $from,
        mixed  $mode,
        mixed  $files,
        mixed  $selector
    ): array
    {
        return $this
            ->from(
                from: $from
            )->mode(
                mode: $mode,
                files: $files,
                selector: $selector
            )->get();
    }

    protected function from(
        $from
    ): InBlueprintFactory
    {
        $this->from = $from;
        return $this;
    }

    protected function mode(
        mixed $mode,
        mixed $files,
        mixed $selector
    )
    {
        $this->mode = "\App\Blueprint\Handler\\{$mode}";

        $this->results = app($this->mode)
            ->handle($files, $selector)
            ->run();
        return $this;
    }

    public function get()
    {
        return $this->results;
    }
}
