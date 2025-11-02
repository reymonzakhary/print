<?php

namespace App\Blueprints\Processors;

use App\Blueprints\Snippets\Snippet;
use App\Blueprints\Validations\Validator;
use Illuminate\Support\Str;

class MatchStringWithReplace
{

    /**
     * @param string $pattern
     * @param string $string
     * @param array  $array
     * @return array
     */
    public static function inlineStrings(
        string $pattern,
        string $string,
        array  $array = [],
    ): array
    {
        $found = [];
        $r = [];
        $s = [];
        collect(Validator::hasMatches($string, $pattern, 'MatchStringWithReplace', $array))
            ->each(function ($match) use ($array, &$found, &$r, &$s) {
                if (Str::contains($match, "\t")) {
                    $search = explode("\t", trim($match));
                    foreach ($search as $k => $v) {
                        $replace = preg_replace_callback('/\[[A-Za-z0-9\s]*\]/i', static function ($select) use ($array) {
                            $k = Str::lower(Str::replace(['[', ']', ' '], ['', '', '_'], $select[0]));
                            if ($array[$k]) {
                                return $array[$k];
                            }
                        }, $v);
                        $counter = strlen($v) < strlen($replace) ?
                            strlen(strlen($replace) - strlen($v)) : 1;
                        $spaces = str_repeat("   ", $counter + 1);
                        $s = array_unique(array_merge($s, [$v]));
                        if ($k === 0) {
                            $r = array_unique(array_merge($r, [$replace . ' ']));
                        } else {
                            $r = array_unique(array_merge($r, [$spaces . "\t" . $replace . ' ']));
                        }
                    }
                } else {
                    $replace = preg_replace_callback('/\[[A-Za-z0-9\s]*\]/i', function ($select) use ($array) {
                        $k = Str::lower(Str::replace(['[', ']', ' '], ['', '', '_'], $select[0]));
                        if (optional($array)[$k]) {
                            return $array[$k];
                        }
                    }, $match);
                    $s = array_unique(array_merge($s, [$match]));
                    $r = array_unique(array_merge($r, [$replace . ' ']));
                }

                $found['search'] = $s;
                $found['replace'] = $r;
            });
        return $found;
    }

    /**
     * @param array  $patterns
     * @param string $string
     * @param array  $array
     * @return array
     */
    public static function replacer(
        array  $patterns,
        string $string,
        array  $array = [],
    ): array
    {
        $found = [];
        collect($patterns)->map(function ($pattern) use ($array, $string, &$found) {
            foreach ($pattern->args as $p) {
                $found['search'][] = collect(
                    Validator::hasMatches($string, '/' . $p->key . '/i', 'MatchStringWithReplace', $array, $p->select)
                )->unique()->first();
                if (preg_match('/\[!(\w+)\]/i', Str::lower($p->value), $snippet)) {
                    $found['replace'][] = Snippet::{Str::lower($snippet[1])}($p->max_width, $array) . ' ';
                } else {
                    $found['replace'][] = Validator::HasValueFromRegExp($p->value, "/\[(.*?)]/", 'MatchStringWithReplace', $array) . ' ';
                }
            }
        });

        return $found;
    }

    /**
     * @param string $pattern
     * @param string $string
     * @param array  $array
     * @return array
     */
    public static function single(
        string $pattern,
        string $string,
        array  $array = [],
    ): array
    {
        $found = [];
        $search = [];
        $replace = [];
        collect(Validator::hasMatches($string, $pattern, 'MatchStringWithReplace', $array))
            ->each(function ($match) use ($array, &$found, &$search, &$replace) {

                if (!in_array($match, $search, true)) {
                    $search = array_unique(array_merge($search, [$match]));
                    if (preg_match('/\[!(.*?)]/', $match, $snippet)) {
                        $replace = array_merge($replace, [Snippet::{Str::lower($snippet[1])}(30, $array) ?? '']);
                    } else {
                        $replace = array_merge($replace, [optional($array)[Str::lower(Str::replace(['[', ']', ' '], ['', '', '_'], $match))] ?? '']);
                    }
                }
                $found['search'] = $search;
                $found['replace'] = $replace;
            }
            );
        return $found;
    }

}
