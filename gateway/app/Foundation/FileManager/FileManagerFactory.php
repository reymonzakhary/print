<?php

namespace App\Foundation\FileManager;

use App\Foundation\FileManager\Contracts\FileManagerInterface;
use App\Models\Tenants\Media\FileManager;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class FileManagerFactory implements FileManagerInterface
{
    protected static FileManager $media;

    /**
     * @param Model  $subject
     * @param string $key
     * @return mixed
     */
    public static function createFromRequest(
        Model  $subject,
        string $key
    ): mixed
    {
        return static::createMultipleFromRequest($subject, [$key])->first();
    }

    /**
     * @param Model $subject
     * @param array $keys
     * @param string|null $disk
     * @return Collection
     */
    public static function createMultipleFromRequest(
        Model $subject,
        array $keys = [],
        null|string $path = null,
        null|string $disk = null
    ): Collection
    {
        return collect($keys)
            ->map(function (string $key) use ($subject, $path, $disk) {
                $search = ['[', ']', '"', "'"];
                $replace = ['.', '', '', ''];

                $key = str_replace($search, $replace, $key);

                if (!request()->hasFile($key)) {
                    throw ValidationException::withMessages([$key => "{$key} was not found"]);
                }

                $files = request()->file($key);

                if (!is_array($files)) {
                    return static::create($subject, $files,$path, $disk);
                }

                return array_map(fn($file) => static::create($subject, $file,$path, $disk), $files);
            })->flatten();
    }


    /**
     * @param Model $model
     * @param string|UploadedFile $file
     * @param string|null $path
     * @param string|null $disk
     * @return Model
     */
    public static function create(
        Model               $model,
        string|UploadedFile $file,
        string|null $path = null,
        string|null $disk = null
    ): Model
    {
        $fileAdder = app(FileAdder::class);
        return $fileAdder->setSubject($model)
            ->setFile($file)
            ->setPath($path)
            ->setDisk($disk)
            ->toMediaCollection();
    }

    public function drop(
        Model $model,
    )
    {
        $model->media()->detach();
        return $this;
    }


    /*
     * Set the file that needs to be imported.
     *
     * @param string|\Symfony\Component\HttpFoundation\File\UploadedFile $file
     *
     * @return $this
     */
    public function setFile($file): self
    {
        $this->file = $file;

        if (is_string($file)) {
            $this->pathToFile = $file;
            $this->setFileName(pathinfo($file, PATHINFO_BASENAME));
            $this->mediaName = pathinfo($file, PATHINFO_FILENAME);

            return $this;
        }

        if ($file instanceof RemoteFile) {
            $this->pathToFile = $file->getKey();
            $this->setFileName($file->getFilename());
            $this->mediaName = $file->getName();

            return $this;
        }

        if ($file instanceof UploadedFile) {
            $this->pathToFile = $file->getPath() . '/' . $file->getFilename();
            $this->setFileName($file->getClientOriginalName());
            $this->mediaName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);

            return $this;
        }

        if ($file instanceof SymfonyFile) {
            $this->pathToFile = $file->getPath() . '/' . $file->getFilename();
            $this->setFileName(pathinfo($file->getFilename(), PATHINFO_BASENAME));
            $this->mediaName = pathinfo($file->getFilename(), PATHINFO_FILENAME);

            return $this;
        }

        if ($file instanceof TemporaryUpload) {
            return $this;
        }

//        throw UnknownType::create();
    }


}
