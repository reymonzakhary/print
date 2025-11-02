<?php

namespace App\Foundation\Media\Traits;

use Alexusmai\LaravelFileManager\Services\ACLService\ACL;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use League\Flysystem\AwsS3V3\AwsS3V3Adapter;
use League\Flysystem\FilesystemException;

trait ContentTrait
{
    /**
     * Get content for the selected disk and path
     *
     * @param      $disk
     * @param null $path
     *
     * @return array
     */
    public function getContent($disk, $path = null): array
    {
        $content = Storage::disk($disk)->listContents($path ?: '')->toArray();

        $directories = $this->filterDir($disk, $content);
        $files = $this->filterFile($disk, $content);

        return compact('directories', 'files');
    }

    /**
     * Get directories with properties
     *
     * @param $disk
     * @param  null  $path
     *
     * @return array
     * @throws FilesystemException
     */
    public function directoriesWithProperties($disk, $path = null): array
    {
        $content = Storage::disk($disk)->listContents($path ?: '')->toArray();

        return $this->filterDir($disk, $content);
    }

    /**
     * Get files with properties
     *
     * @param       $disk
     * @param null  $path
     *
     * @return array
     */
    public function filesWithProperties($disk, $path = null): array
    {
        $content = Storage::disk($disk)->listContents($path ?: '');

        return $this->filterFile($disk, $content);
    }

    /**
     * Get directories for tree module
     *
     * @param $disk
     * @param  null  $path
     *
     * @return array
     * @throws FilesystemException
     */
    public function getDirectoriesTree($disk, $path = null): array
    {
        $directories = $this->directoriesWithProperties($disk, $path);
        $tenant = explode('/', $path)[0];
        foreach ($directories as $index => $dir) {
            $directories[$index]['props'] = [
                'hasSubdirectories' => (bool) Storage::disk($disk)->directories($tenant . '/' .$dir['path']),
            ];
        }

        return $directories;
    }

    /**
     * Get the basic properties of a file
     *
     * @param string $filePath
     *
     * @return array
     */
    private function fileBasicProperties(
        string $filePath
    ): array
    {
        $pathInfo = pathinfo($filePath);

        return [
            'type' => 'file',
            'path' => $filePath,
            'basename' => $pathInfo['basename'],
            'dirname' => $pathInfo['dirname'] === '.' ? '' : $pathInfo['dirname'],
            'extension' => $pathInfo['extension'] ?? '',
            'filename' => $pathInfo['filename'],
        ];
    }

    /**
     * Get the full properties of a file
     *
     * @param string $disk
     * @param string $path
     *
     * @return mixed
     */
    public function fileProperties(
        string $disk,
        string $path
    ): mixed
    {
        $properties = array_merge(
            $this->fileBasicProperties($path),
            [
                'size' => Storage::disk($disk)->size($path),
                'mime-type' => Storage::disk($disk)->mimeType($path),
                'timestamp' => Storage::disk($disk)->lastModified($path),
                'visibility' => Storage::disk($disk)->getVisibility($path),
            ]
        );

        // if ACL ON
        if ($this->configRepository->getAcl()) {
            return $this->aclFilter($disk, [$properties])[0];
        }

        return $properties;
    }

    /**
     * Get properties for the selected directory
     *
     * @param       $disk
     * @param  null  $path
     *
     * @return array|false
     */
    public function directoryProperties($disk, $path = null): bool|array
    {
        $adapter = Storage::drive($disk)->getAdapter();

        $pathInfo = pathinfo($path);

        $properties = [
            'type'       => 'dir',
            'path'       => $path,
            'basename'   => $pathInfo['basename'],
            'dirname'    => $pathInfo['dirname'] === '.' ? '' : $pathInfo['dirname'],
            'timestamp'  => $adapter instanceof AwsS3V3Adapter ? null : Storage::disk($disk)->lastModified($path),
            'visibility' => $adapter instanceof AwsS3V3Adapter ? null : Storage::disk($disk)->getVisibility($path),
        ];

        // if ACL ON
        if ($this->configRepository->getAcl()) {
            return $this->aclFilter($disk, [$properties])[0];
        }

        return $properties;
    }

    /**
     * Get only directories
     *
     * @param $disk
     * @param $content
     *
     * @return array
     */
    protected function filterDir($disk, $content): array
    {
        // select only dir
        $dirsList = array_filter($content, fn($item) => $item['type'] === 'dir');

        $dirs = array_map(function ($item) {
            $pathInfo = pathinfo($item['path']);

            return [
                'type'       => $item['type'],
                'path'       => ltrim(Str::replace(tenant()->uuid, '', $item['path']), '/'),
                'basename'   => $pathInfo['basename'],
                'dirname'    => $pathInfo['dirname'] === '.' ? '' :
                    ltrim(Str::replace(tenant()->uuid, '', $pathInfo['dirname']), '/'),
                'timestamp'  => $item['lastModified'],
                'visibility' => $item['visibility'],
            ];
        }, $dirsList);

        // if ACL ON
        if ($this->configRepository->getAcl()) {
            return array_values($this->aclFilter($disk, $dirs));
        }

        return array_values($dirs);

    }

    /**
     * Get only files
     *
     * @param $disk
     * @param $content
     *
     * @return array|\Illuminate\Support\Collection
     */
    protected function filterFile($disk, $content)
    {
        // select only dir
        $filesList = array_filter($content, fn($item) => $item['type'] === 'file');

        $files = array_map(function ($item) {
            $pathInfo = pathinfo($item['path']);

            return [
                'type'       => $item['type'],
                'path'       => ltrim(Str::replace(tenant()->uuid, '', $item['path']), '/'),
                'basename'   => $pathInfo['basename'],
                'dirname'    => $pathInfo['dirname'] === '.' ? '' :
                    ltrim(Str::replace(tenant()->uuid, '', $pathInfo['dirname']), '/'),
                'extension'  => $pathInfo['extension'] ?? '',
                'filename'   => $pathInfo['filename'],
                'size'       => $item['fileSize'],
                'timestamp'  => $item['lastModified'],
                'visibility' => $item['visibility'],
            ];
        }, $filesList);

        // if ACL ON
        if ($this->configRepository->getAcl()) {
            return collect(array_values($this->aclFilter($disk, $files)))->map(function ($item) {
                $item['path'] = ltrim(Str::replace(tenant()->uuid, '', $item['path']), '/');
                $item['dirname'] = ltrim(Str::replace(tenant()->uuid, '', $item['dirname']), '/');
                return $item;
            });
        }

        $paths = collect($files)
            ->keyBy('path')
            ->keys()
            ->map(fn($k) => ltrim(Str::replace(tenant()->uuid, '', $k), '/'))
            ->toArray();
        $paths = request()->fm::whereIn('name', $paths)?->with('tags')->get()->toArray();

        $res = [];
        foreach ($files as $file) {

            $file['path'] = ltrim(Str::replace(tenant()->uuid, '', $file['path']), '/');
            $file['dirname'] = ltrim(Str::replace(tenant()->uuid, '', $file['dirname']), '/');


            $file['tags'] = collect($paths)
                ->filter(fn($f) => $f['name'] === $file['path'])
                ->pluck('tags')
                ->flatten(1)
                ->map(fn($tag) => [
                    'id' => $tag['row_id'],
                    'name' => $tag['name'],
                    'slug' => $tag['slug'],
                    'hex' => $tag['hex'],
                    'iso' => $tag['iso'],
                ]);
            $res[] = $file;
        }

        return $res;
    }

    /**
     * ACL filter
     *
     * @param $disk
     * @param $content
     *
     * @return mixed
     */
    protected function aclFilter($disk, $content): mixed
    {
        $acl = resolve(ACL::class);
        $withAccess = array_map(function ($item) use ($acl, $disk) {
            // add acl access level
            $path = ltrim(Str::replace(tenant()->uuid, '', $item['path']), '/');
            $item['acl'] = $acl->getAccessLevel($disk, $path);

            return $item;
        }, $content);

        // filter files and folders
        if ($this->configRepository->getAclHideFromFM()) {
            return array_filter($withAccess, function ($item) {
                $item['path'] = ltrim(Str::replace(tenant()->uuid, '', $item['path']), '/');
                $item['dirname'] = ltrim(Str::replace(tenant()->uuid, '', $item['dirname']), '/');
                return $item['acl'] !== 0;
            });
        }

        return $withAccess;
    }

    /**
     * @param string $fullPath
     * @param string $tenantUuid
     * @return string
     */
    private function removeTenantPrefixingFromPath(
        string $fullPath,
        string $tenantUuid
    ): string
    {
        return ltrim(str_replace($tenantUuid, '', $fullPath), '/');
    }

    /**
     * Escape some special characters in file or directory name
     *
     * @param string $fileOrDirectoryPath
     *
     * @return string
     */
    private function escapeFileOrDirectoryNameInThePath(
        string $fileOrDirectoryPath
    ): string
    {
        $charsToEscape = ['!', '@', '#', '$', '%', '^', '&', '*'];

        $fileOrDirectoryName = pathinfo($fileOrDirectoryPath, PATHINFO_FILENAME);
        $escapedFileOrDirectoryName = Str::replace($charsToEscape, '-', $fileOrDirectoryName);

        return Str::replace($fileOrDirectoryName, $escapedFileOrDirectoryName, $fileOrDirectoryPath);
    }
}
