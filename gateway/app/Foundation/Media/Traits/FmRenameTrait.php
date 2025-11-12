<?php

declare (strict_types=1);

namespace App\Foundation\Media\Traits;

use App\Events\Tenant\FM\RenameEvent;
use App\Foundation\Media\MediaType;
use App\Models\Tenant\Media\FileManager as FileManagerModel;
use Hyn\Tenancy\Contracts\Website;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use LogicException;

trait FmRenameTrait
{
    /**
     * Rename a directory recursively
     *
     * @param string $diskName
     * @param string $directoryOldPath
     * @param string $directoryNewPath
     * @param Website $tenant
     *
     * @return void
     */
    private function renameDirectory(
        string  $diskName,
        string  $directoryOldPath,
        string  $directoryNewPath,
        Website $tenant
    ): void
    {
        $disk = Storage::disk($diskName);

        [$allChildFilesRecursively, $allChildDirectoriesRecursively] = [
            $disk->allFiles($directoryOldPath),
            $disk->allDirectories($directoryOldPath)
        ];

        if (empty($allChildDirectoriesRecursively) && empty($allChildFilesRecursively)) {
            # Current directory is totally empty, Just move it (Delete & Re-create)

            $this->renameEmptyDirectory($diskName, $directoryOldPath, $directoryNewPath);
        } else {
            # Current directory has child files/directories. Start the moving process

            # Loop over all the child files and rename each one
            foreach ($allChildFilesRecursively as $filePath) {
                $this->renameFile(
                    $diskName,
                    $filePath,
                    Str::replace($directoryOldPath, $directoryNewPath, $filePath),
                    $tenant
                );
            }

            $remainingDirectoriesToRename = count($allChildDirectoriesRecursively) > 0 ?
                array_diff($allChildDirectoriesRecursively, $disk->allDirectories($directoryNewPath)) :
                [];

            # Loop over all child empty directories and rename each one
            foreach ($remainingDirectoriesToRename as $remainingDirectoryPath) {
                $this->renameEmptyDirectory(
                    $diskName,
                    $remainingDirectoryPath,
                    Str::replace($directoryOldPath, $directoryNewPath, $remainingDirectoryPath)
                );
            }

            $disk->deleteDirectory($directoryOldPath) ?:
                throw new LogicException(
                    __('Could not delete the directory on path ":directory_path" from disk ":disk_name"', [
                        'directory_path' => $directoryOldPath,
                        'disk_name' => $diskName
                    ]));
        }

        event(new RenameEvent([
            "disk" => $diskName,
            "oldName" => $directoryOldPath,
            "newName" => $directoryNewPath
        ]));
    }

    /**
     * Rename an empty directory
     *
     * @param string $diskName
     * @param string $directoryOldPath
     * @param string $directoryNewPath
     *
     * @return void
     */
    private function renameEmptyDirectory(
        string $diskName,
        string $directoryOldPath,
        string $directoryNewPath
    ): void
    {
        $disk = Storage::disk($diskName);

        $disk->makeDirectory($directoryNewPath) ?:
            throw new LogicException(
                __('Could not create the directory on path ":directory_path" on disk ":disk_name"', [
                    'directory_path' => $directoryNewPath,
                    'disk_name' => $diskName
                ]));

        $disk->deleteDirectory($directoryOldPath) ?:
            throw new LogicException(
                __('Could not delete the directory on path ":directory_path" from disk ":disk_name"', [
                    'directory_path' => $directoryOldPath,
                    'disk_name' => $diskName
                ]));
    }

    /**
     * Rename a single file and update its metadata in the database
     *
     * @param string $diskName
     * @param string $fileOldPath
     * @param string $fileNewPath
     * @param Website $tenant
     *
     * @return void
     */
    private function renameFile(
        string  $diskName,
        string  $fileOldPath,
        string  $fileNewPath,
        Website $tenant
    ): void
    {
        DB::transaction(function () use ($diskName, $fileOldPath, $fileNewPath, $tenant) {
            [$fileBasicPropertiesOld, $fileBasicPropertiesNew] = [
                $this->fileBasicProperties($fileOldPath),
                $this->fileBasicProperties($fileNewPath)
            ];

            $fileDbEntry = FileManagerModel::where([
                ['disk', $diskName],
                ['path', $this->removeTenantPrefixingFromPath($fileBasicPropertiesOld['dirname'], $tenant->uuid)],
                ['name', $fileBasicPropertiesOld['basename']],
                ['ext', $fileBasicPropertiesOld['extension']],
            ])->first();

            $fileDbEntry ?
                $fileDbEntry->updateOrFail([
                    'user_id' => auth()->id(),
                    'name' => $fileBasicPropertiesNew['basename'],
                    'group' => MediaType::getGroupType($fileBasicPropertiesNew['extension']),
                    'path' => $this->removeTenantPrefixingFromPath($fileBasicPropertiesNew['dirname'], $tenant->uuid),
                    'ext' => $fileBasicPropertiesNew['extension'],
                ]) :
                $this->nullLogger->warning('Going to rename a file that does not have any entry in the database', [
                    'disk_name' => $diskName,
                    'old_path' => $fileOldPath,
                    'new_path' => $fileNewPath
                ]);

            Storage::disk($diskName)->move($fileOldPath, $fileNewPath) ?:
                throw new LogicException(
                    __('Could not move the file from ":from_path" to ":to_path" on disk ":disk_name"', [
                        'from_path' => $fileOldPath,
                        'to_path' => $fileNewPath,
                        'disk_name' => $diskName,
                    ]),
                );

            event(new RenameEvent([
                "disk" => $diskName,
                "oldName" => $fileOldPath,
                "newName" => $fileNewPath
            ]));
        });
    }
}
