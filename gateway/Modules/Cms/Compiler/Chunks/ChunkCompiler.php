<?php


namespace Modules\Cms\Compiler\Chunks;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Modules\Cms\Entities\Chunk;

class ChunkCompiler
{
    /**
     * @param string $html
     * @return string
     */
    public static function getChunk(
        string $html
    ): string
    {
        return preg_replace_callback('/\[\[\$(.*?)]]/', function ($variable) {
            if (count($variable) > 0) {
                return self::getChunkFromCacheOrDB($variable[1])?->content;
            }
        }, $html);
    }

    public static function getChunkFromCacheOrDB($chunkName)
    {
        return Cache::get(tenant()->uuid.'.chunk.'.$chunkName)
                ?? Chunk::where('name', $chunkName)->first();
    }

    /**
     * @param string $html
     * @return string
     */
    public static function flatHtml(
        string $html
    )
    {
        $count = 0;
        $continue = self::find($html);
        while ($continue) {
            $html = self::getChunk($html);
            $count++;
            if ($count > 10) {
                $continue = false;
            }
        }
        return $html;
    }

    /**
     * @param $html
     * @return bool
     */
    protected static function find($html): bool
    {
        return preg_match('/\[\[\$(.*?)]]/', $html);
    }

}
