<?php


namespace App\Foundation\Media;


class MediaType
{
    private const TYPES = [
        'image/jpeg' => 'images',
        'image/png' => 'images',
        'application/xml' => 'xml',
        'application/pdf' => 'pdf',
        'application/mpeg' => 'videos',
        'video/mp4' => 'videos',
        'video/quicktime' => 'videos'

    ];

    /**
     * @param string $type
     * @return string
     */
    public static function getGroupType(
        string $type
    ): string
    {
        return self::TYPES[$type] ?? 'others';
    }
}
