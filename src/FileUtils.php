<?php
namespace Nyrados\Utils\File;

use InvalidArgumentException;
use LogicException;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UriFactoryInterface;
use Psr\Http\Message\UriInterface;

class FileUtils
{
    public const

        REGEX_WRAPPERNAME = '[a-z][a-z0-9+\-.]+',
        REGEX_WINDOWSDRIVE = '[a-zA-Z]{1}:',

        REGEX_IS_ABSOLUTE = '^(\/|[a-zA-Z]:[\/]?)',
        REGEX_IS_ROOT = self::REGEX_IS_ABSOLUTE . '$'
    ;

    /**
     * Normalizes a File Path Simple Path.
     *
     * Things that will be normalized:
     *
     * - Backlashes & urlencoded Backslashes to normal Slashes
     * - Path will be trimmed
     * - A Endlash will be removed (if its not a root directory)
     * - Windows Drive without a slash gets one ( C: => C:/ )
     * - A Slash before a windows drive gets removed (/C:/ => C:/)
     * - Single Dots will be removed
     *   - ./a/b => a/b
     *   - /a/./b => /a/b
     *   - /a/b/. => /a/b
     *
     * @param string|Path $path
     * @return string
     */
    public static function normalizePath($path): string
    {
        if ($path instanceof Path) {
            return $path->getPath();
        }

        $path = trim((string) $path);

        foreach (['\\', \urlencode('\\')] as $replace) {
            $path = \str_replace($replace, '/', $path);
        }

        $path = preg_replace('/(^\.\/)|(\/\.$)/', '', $path);
        $path = preg_replace('/\/\.\//', '/', $path);
        $path = preg_replace('/([\/]?)(' . self::REGEX_WINDOWSDRIVE . ')(\/|$)/', '$2/', $path);

        if (empty($path) || $path == '.') {
            return '';
        }

        if (substr($path, -1) == '/' && $path != '/') {
            $path = substr($path, 0, -1);
        }

        if (preg_match('/^' . self::REGEX_WINDOWSDRIVE . '$/', $path)) {
            $path .= '/';
        }

        return $path;
    }

    public static function isUri($uri)
    {
        return preg_match('/^' . FileUtils::REGEX_WRAPPERNAME . ':\//', (string) $uri);
    }

    public static function removeBackwards($path): string
    {
        $i = 0;
        $dirs = explode("/", $path);
        $rs = [];

        for ($j = sizeof($dirs) - 1; $j >= 0; --$j) {
            if (trim($dirs[$j]) ==="..") {
                $i++;
            } else {
                if ($i > 0) {
                    $i--;
                } else {
                    $rs[] = $dirs[$j];
                }
            }
        }

        return $path;
    }
}
