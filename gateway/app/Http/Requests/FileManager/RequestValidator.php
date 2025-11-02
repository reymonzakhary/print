<?php

declare(strict_types=1);

namespace App\Http\Requests\FileManager;

use Alexusmai\LaravelFileManager\Services\ConfigService\ConfigRepository;
use Closure;
use Exception;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Interfaces\ImageManagerInterface;

final class RequestValidator extends FormRequest
{
    use CustomErrorMessage;

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        $config = resolve(ConfigRepository::class);
        $imageManager = app(ImageManagerInterface::class);
        return [
            'disk' => [
                'string',
                function (string $attribute, string $value, Closure $fail) use ($config): void {
                    if (!in_array($value, $config->getDiskList(), true) ||
                        !array_key_exists($value, config('filesystems.disks'))
                    ) {
                        $fail(__('We couldn\'t find disk configuration.'));
                    }
                },
            ],

            'originalPath' => [
                'nullable',
                'string',
                function (string $attribute, string $value, Closure $fail): void {
                    if ($value && tenant()->uuid . '/' !== $value) {
                        if (!Storage::disk($this->input('disk'))->exists($value)
                        ) {
                            $fail(__('We couldn\'t find the specified path or directory.'));
                        }
                    }
                },
            ],

            'path' => 'nullable|string|sometimes',
            'thumbsize' => 'nullable|integer|max:300',
            'is_last' => 'nullable|boolean',

            'newName' => 'string|sometimes',
            'oldName' => 'string|sometimes',

            'files' => 'nullable|array',
            'files.*' => [
                'max:2048000',
                function (string $attribute, UploadedFile $uploadedFile, Closure $fail) use ($imageManager): void {
                    if (!$uploadedFile->isValid()) {
                        $fail(
                            sprintf(
                                'You have uploaded an invalid file. Error-Message: %s',
                                $uploadedFile->getErrorMessage()
                            )
                        );
                    }

                    if (in_array($uploadedFile->getClientOriginalExtension(), Config::get('file-manager.extensionsToConsiderFilesAsImages'), true)) {
                        try {
                            $imageManager->read($uploadedFile->get());
                        } catch (Exception $e) {
                            $fail(
                                sprintf(
                                    'Looks like you have tried to upload an image file but with corrupted binary data. Intervention-Error: %s',
                                    $e->getMessage()
                                )
                            );
                        }
                    }
                }
            ],

            'items' => 'nullable|array',
            'items.*.path' => 'string',
            'items.*.type' => 'string',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'originalPath' => urldecode(str_replace('//', '/', $this->originalPath)),
            'path' => urldecode((string)$this->request->get('path'))
        ]);
    }
}
