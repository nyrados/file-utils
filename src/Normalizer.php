<?php
namespace Nyrados\Utils\File;

class Normalizer
{
    public const
        NORMALIZE_ABSOLUTE = 1,
        NORMALIZE_RELATIVE = 2,
        NORMALIZE_END_SLASH = 4,
        NORMALIZE_NO_END_SLASH = 8,
        NORMALIZE_REALPATH = 16,

        REGEX_FILENAME = '[\w\-.]'
    ;

    public static function normalize(string $path, $flags = 0): string 
    {
        foreach (['\\', \urlencode('\\'), '//'] as $replace) {
            $path = \str_replace($replace, '/', $path);
        }

        if ($flags & self::NORMALIZE_END_SLASH && substr($path, -1) != '/') {
            $path = $path . '/';
        }

        if($flags & self::NORMALIZE_NO_END_SLASH && substr($path, -1) == '/') {
            $path = substr($path, 0, -1);
        }

        if ($flags & self::NORMALIZE_ABSOLUTE && ( empty($path) || $path[0] != '/') ) {
            $path = '/' . $path;
        }

        if($flags & self::NORMALIZE_RELATIVE && !empty($path) && $path[0] == '/' && $path != '/') {
            $path = substr($path, 1);      
        }

        return $path;
    }


    private function __constrcut()
    {

    }

}