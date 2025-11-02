<?php

namespace App\Blueprint\Validators;

use File;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class ValidateFileRequest
{

    /**
     * @throws ValidationException
     */
    public function __construct(
        public Request $request,
        public array   $validate,
        public         $key,
        public         $from,
        public         $model,
        public         $selector,
        public         $lookup
    )
    {
        $this->validate();
        $this->render();
    }

    /**
     * @throws ValidationException
     */
    protected function validate(): void
    {
//        $validator = Validator::make($this->request->all(), $this->validate);
//        if($validator->fails()) {
//            throw ValidationException::withMessages([
//                optional($validator->errors()->keys())[0] => [
//                    $validator->messages()->first()
//                ]
//            ]);
//        }
    }

    /**
     * @return array
     */
    public function render(): array
    {
        $from = $this->from;
        return collect($this->request->$from)
            ->reject(fn($f, $k) => $k !== $this->key)
            ->map(function ($file) {
                if ($file instanceof UploadedFile) {
                    return [
                        'file' => $file,
                        'model' => $this->model,
                        'ns' => '\\' . config('blueprint.mode.' . $this->model),
                        'type' => File::mimeType($file),
                        'selector' => $this->selector,
                        'lookup' => $this->lookup
                    ];
                }
                return [

                ];
            })->toArray();
    }

}
