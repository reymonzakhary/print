<?php

declare(strict_types=1);

namespace App\Jobs\Tenant\Quotations;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

final class DeleteEntityFromDiskJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(
        private readonly string $disk,
        private readonly string $entityFullPath,
        private readonly bool $isFileEntity = true
    ) {
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $diskObject = Storage::disk($this->disk);

        if (!$diskObject->exists($this->entityFullPath)) {
            throw new RuntimeException(
                sprintf(
                    'Given entity "%s" does not exist on disk "%s"',
                    $this->entityFullPath,
                    $this->disk
                )
            );
        }

        $result = match ($this->isFileEntity) {
            true => $diskObject->delete($this->entityFullPath),
            false => $diskObject->deleteDirectory($this->entityFullPath),
        };

        if (false === $result) {
            throw new RuntimeException(
                sprintf(
                    'Could not delete entity on path "%s" from disk "%s"',
                    $this->entityFullPath,
                    $this->disk
                )
            );
        }
    }
}
