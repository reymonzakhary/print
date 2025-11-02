<?php

namespace App\Blueprints\Actions;

use App\Blueprints\Contracts\Abstracts\Action;
use App\Blueprints\Contracts\ActionContractInterface;
use FilesystemIterator;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RuntimeException;
use SplFileInfo;
use ZipArchive;

class ZipFilesAction extends Action implements ActionContractInterface
{

    /**
     * {
     * "id": 9,
     * "uses": "action",
     * "event": {},
     * "action": {
     * "as": "zipped",
     * "file": false,
     * "input": {
     * "from": "MergeFilesAction.merged"
     * },
     * "model": "ZipFilesAction"
     * },
     * "active": true,
     * "assets": [],
     * "decision": false,
     * "queueable": false,
     * "transition": {}
     * },
     * @return mixed|void
     */
    public function handle()
    {

        $path = "{$this->signature}/output";
        $unique = Str::random(4);
        $zip_file = "{$unique}_{$this->request->get('attachment_destination')}_output.zip";
        if($this->request->get('attachment_to') !== 'self') {
            $zip_file = $this->request->get('has_main') ?
            "{$unique}_{$this->request->get('main_attachment_destination')->slug}_output.zip":
            "{$unique}_{$this->request->get('attachment_destination')->slug}_output.zip";
        }

        $filePath = Storage::disk('local')->path("{$path}/{$zip_file}");

        $zip = new ZipArchive();

        if (!$zip->open($filePath, ZipArchive::CREATE)) {
            throw new RuntimeException('Cannot open ' . $filePath);
        }

        $this->addContent($zip, realpath(Storage::disk('local')->path($path)));

        $zip->close();

        $this->output = [
            "path" => "{$path}/{$zip_file}",
            "url" => asset("{$path}/{$zip_file}"),
            "disk" => "local"
        ];
    }


    /**
     * This takes symlinks into account.
     *
     * @param ZipArchive $zip
     * @param string     $path
     */
    private function addContent(ZipArchive $zip, string $path): void
    {
        /** @var SplFileInfo[] $files */
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator(
                $path,
                FilesystemIterator::FOLLOW_SYMLINKS
            ),
            RecursiveIteratorIterator::SELF_FIRST
        );

        while ($iterator->valid()) {
            if (!$iterator->isDot()) {
                $filePath = $iterator->getPathName();
                $relativePath = substr($filePath, strlen($path) + 1);

                if (!$iterator->isDir()) {
                    $zip->addFile($filePath, $relativePath);
                } else {
                    if ($relativePath !== false) {
                        $zip->addEmptyDir($relativePath);
                    }
                }
            }
            $iterator->next();
        }
    }
}
