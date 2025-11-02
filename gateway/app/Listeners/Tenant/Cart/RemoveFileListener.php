<?php

namespace App\Listeners\Tenant\Cart;

use Illuminate\Support\Facades\Storage;

class RemoveFileListener
{
    /**
     * Handle the event.
     *
     * @param object $event
     * @return void
     */
    public function handle($event)
    {
        collect($event->files)->map(function ($file) {
            if ($file['type'] === 'dir') {
                if (Storage::disk($file['disk'])->exists($file['path'])) {
                    Storage::disk($file['disk'])->deleteDirectory($file['path']);
                }
            } else {
                if (Storage::disk($file['disk'])->exists($file['path'] . DIRECTORY_SEPARATOR . $file['name'])) {
                    Storage::disk($file['disk'])->delete($file['path'] . DIRECTORY_SEPARATOR . $file['name']);
                }
            }
        });
    }
}
