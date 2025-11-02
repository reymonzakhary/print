<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Closure;
use Exception;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Interfaces\ImageManagerInterface;

trait MediaImageValidatorTrait
{

    private function validateMediaItemsAsImages(string $attribute, string $mediaFileRelativePath, Closure $fail): void
    {
        $mediaFileFullPath = sprintf('%s/%s', tenant()->uuid, $mediaFileRelativePath);
        $storageDisk = Storage::disk('assets');

        # Check if the image exist in the media manager
        if (!$storageDisk->exists($mediaFileFullPath)) {
            $fail(
                sprintf('Given image "%s" does not exist on the "%s" disk.', $mediaFileFullPath, 'assets')
            );
        }

        # Try to load the image to ensure its validity
        try {
            app(ImageManagerInterface::class)->read($storageDisk->get($mediaFileFullPath));
        } catch (Exception $e) {
            $fail(
                sprintf(
                    'Looks like you have tried to set an invalid or a corrupted image. Intervention-Error: %s',
                    $e->getMessage()
                )
            );
        }
    }
}
