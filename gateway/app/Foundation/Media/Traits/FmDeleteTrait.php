<?php

declare(strict_types=1);

namespace App\Foundation\Media\Traits;

use App\Models\Tenants\Media\FileManager as FileManagerModel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use LogicException;

trait FmDeleteTrait
{
    /**
     * @param string $disk
     * @param string $filePath
     * @param string $tenant
     * @return void
     */
    private function deleteFile(
        string $disk,
        string $filePath,
        string $tenant
    ): void
    {
        DB::transaction(function () use ($disk, $filePath, $tenant) {
            $fileProperties = $this->fileProperties($disk, $filePath);

            $fileDbEntry = FileManagerModel::where([
                ['disk', $disk],
                ['path', $this->removeTenantPrefixingFromPath($fileProperties['dirname'], $tenant)],
                ['name', $fileProperties['basename']],
                ['ext', $fileProperties['extension'] ?? ''], # As some files may not have an extension
            ])->first();

            $fileDbEntry ?
                $fileDbEntry->deleteOrFail() :
                $this->nullLogger->warning('Going to delete a file that does not have any entry in the database', [
                    'storage_disk_name' => $disk,
                    'file_path' => $filePath,
                ]);

            Storage::disk($disk)->delete($filePath) ?:
                throw new LogicException(sprintf('Could not delete the file on path "%s".', $filePath));
        });
    }

    /**
     * @param string $disk
     * @param string $directoryPath
     * @param string $tenant
     * @return void
     */
    private function deleteDirectory(
        string $disk,
        string $directoryPath,
        string $tenant
    ): void
    {
        DB::transaction(function () use ($disk, $directoryPath, $tenant) {
            $storageDisk = Storage::disk($disk);

            $directoryDbEntries = FileManagerModel::where([
                ['disk', $disk],
                ['path', 'LIKE', sprintf('%s/%%', $this->removeTenantPrefixingFromPath($directoryPath, $tenant))],
            ])->orWhere([
                ['disk', $disk],
                ['path', $this->removeTenantPrefixingFromPath($directoryPath, $tenant)],
            ])->get();

            $directoryDbEntries->count() > 0 ?
                $directoryDbEntries->each->deleteOrFail() :
                (count($storageDisk->allFiles($directoryPath)) > 0) && $this->nullLogger->warning(
                    'Going to delete a directory that contains existing files and does not have any entry in the database', [
                        'storage_disk_name' => $disk,
                        'directory_path' => $directoryPath,
                    ]
                );

            $storageDisk->deleteDirectory($directoryPath) ?:
                throw new LogicException(sprintf('Could not delete the directory on path "%s".', $directoryPath));
        });
    }
}
