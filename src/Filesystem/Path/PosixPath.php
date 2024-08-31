<?php

declare(strict_types=1);

namespace LWP\Filesystem\Path;

class PosixPath extends Path implements PathInterface
{
    use PathChildCaller;


    public const SEPARATORS = ['/'];
    public const DEFAULT_SEPARATOR = '/';
    public const PATH_RESOLUTION_MODE = SearchPath::RESOLVE_FULL;


    // Splits the path into root and search path components.

    public static function splitAtRoot(string $path): array
    {

        if (self::isAbsolute($path)) {

            // When the first character is the root, assuming it's absolute.
            $root = substr($path, 0, 1);
            $search_path = substr($path, 1);

        } else {

            $root = '';
            $search_path = $path;
        }

        return [
            'root' => $root,
            'search_path' => new SearchPath($search_path, self::SEPARATORS, self::DEFAULT_SEPARATOR, SearchPath::NO_EMPTY_SEGMENTS),
        ];
    }


    // Tells if given path is absolute.

    public static function isAbsolute(string $path): bool
    {

        return (!empty($path) && in_array($path[0], self::SEPARATORS));
    }


    // Creates a "FilePath" instance with parameters representing this path type.

    public static function getFilePathInstance(string $path): FilePath
    {

        return new FilePath($path, self::SEPARATORS, self::DEFAULT_SEPARATOR, FilePath::NO_EMPTY_SEGMENTS);
    }
}
