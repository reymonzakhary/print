<?php

namespace App\Foundation\Media\TransferService;

class TransferFactory
{
    /**
     * @param $disk
     * @param $path
     * @param $clipboard
     *
     * @return ExternalTransfer|LocalTransfer
     */
    public static function build($disk, $path, $clipboard): ExternalTransfer|LocalTransfer
    {
        if ($disk !== $clipboard['disk']) {
            return new ExternalTransfer($disk, $path, $clipboard);
        }

        return new LocalTransfer($disk, $path, $clipboard);
    }
}
