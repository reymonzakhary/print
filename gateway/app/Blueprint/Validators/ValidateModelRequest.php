<?php

namespace App\Blueprint\Validators;

class ValidateModelRequest
{
    /**
     * @param mixed  $request
     * @param string $type
     * @param array  $validate
     * @param        $key
     * @param        $from
     * @param        $model
     * @param        $selector
     * @param        $lookup
     */
    public function __construct(
        public mixed  $request,
        public string $type,
        public array  $validate,
        public        $key,
        public        $from,
        public        $model,
        public        $selector,
        public        $lookup
    ) {}

    /**
     * @return array
     */
    public function render(): array
    {
        return [
            $this->key => [
                'from' => $this->from,
                'file' => null,
                'ns' => '\\' . config('blueprint.mode.' . $this->request),
                'model' => $this->model,
                'type' => $this->type,
                'selector' => $this->selector,
                'lookup' => $this->lookup,
            ]
        ];
    }
}
