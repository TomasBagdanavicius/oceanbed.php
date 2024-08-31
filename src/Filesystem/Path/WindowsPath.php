<?php

declare(strict_types=1);

namespace LWP\Filesystem\Path;

class WindowsPath extends Path implements PathInterface
{
    use PathChildCaller;

    public const SEPARATORS = ['\\', '/'];
    public const DEFAULT_SEPARATOR = '\\';
    public const PATH_RESOLUTION_MODE = SearchPath::RESOLVE_FULL;

    public const TYPE_FULL_DOS = 1;
    public const TYPE_DOS = 2;
    public const TYPE_UNC = 3;
    public const TYPE_DOS_DEVICE = 4;


    // Tells if the given path is absolute.

    public static function isAbsolute(string $path): bool
    {

        $first_char = $path[0];

        return (
            in_array($first_char, self::SEPARATORS) // starts with one of the supported separators
            || (
                strlen($path) > 2
                && ctype_alpha($first_char) // first char is alphabetic
                && $path[1] == ':' // second char is colon
                && strspn($path, '/\\', 2, 1) // third char is any kind of slash
            )
        );
    }


    // Splits the path into root, search path, and type components.

    public static function splitAtRoot(string $path): array
    {

        if (self::isAbsolute($path)) {

            $type = '';

            // Starts with a drive letter, eg. "C:".
            if (self::startsWithDriveLetter($path)) {

                if (self::isFullyQualifiedDosPath($path)) {

                    $root_len = 3;
                    $type = self::TYPE_FULL_DOS;

                } else {

                    $root_len = 2;
                    $type = self::TYPE_DOS;
                }

            } else {

                $root_len = 1;

                // Exactly 2 leading slashes, followed by anything but another slash.
                if (strspn($path, '/\\', 0, 2) === 2 && $path[2] != '/' && $path[2] != '\\') {

                    if ($root_len = self::isDOSDevice($path)) {

                        $type = self::TYPE_DOS_DEVICE;

                    } elseif ($root_len = self::isUNC($path)) {

                        $type = self::TYPE_UNC;
                    }
                }
            }

            $root = substr($path, 0, $root_len);
            $search_path = substr($path, $root_len);

        } else {

            $root = $type = '';
            $search_path = $path;
        }

        return [
            'root' => $root,
            'search_path' => new SearchPath($search_path, self::SEPARATORS, self::DEFAULT_SEPARATOR, SearchPath::NO_EMPTY_SEGMENTS),
            'type' => $type,
        ];
    }


    // Tells if the given path is a fully qualified dos path.

    public static function isFullyQualifiedDosPath(string $path)
    {

        return (self::startsWithDriveLetter($path) && in_array($path[2], self::SEPARATORS));
    }


    // Tells if the given path is an UNC path, eg. //Server//Share/foo/bar

    public static function isUNC(string $path)
    {

        if (strspn($path, '/\\', 0, 2) === 2 && !in_array($path[2], self::SEPARATORS)) {

            $path_parts = parent::split(substr($path, 2), self::SEPARATORS, 2);

            if ($path_parts && count($path_parts) > 1) {

                // 3rd member returns position of the endmost slash after the label
                // 2nd member contains the position of the last char in label
                // this allows to chose how trailing slashes should be controled
                return ($path_parts[1][3] + 1);
            }
        }

        return false;
    }


    // Tells if the given path is a DOS device path.

    public static function isDOSDevice(string $path)
    {

        // should either start with "\\.\" or "\\?\"
        if (strspn($path, '/\\', 0, 2) === 2 && ($path[2] == '.' || $path[2] == '?') && ($path[3] == '/' || $path[3] == '\\')) {

            // either "." or "?"
            $special_char = $path[2];
            $path_parts = parent::split(substr($path, 3), self::SEPARATORS, 3);
            $path_parts_count = count($path_parts);

            if ($path_parts && $path_parts_count) {

                // 3rd member returns position of the endmost slash after the label
                // 2nd member contains the position of the last char in label
                // this allows to chose how trailing slashes should be controled

                if ($path_parts[0][1] != 'UNC') {
                    return ($path_parts[0][3] + 1);
                } elseif ($path_parts_count > 2) {
                    return ($path_parts[2][3] + 1);
                }
            }
        }

        return false;
    }


    // Tells if the given path starts with a drive letter, eg. "C:".

    public static function startsWithDriveLetter(string $path): bool
    {

        return (
            strlen($path) > 1 // is at least 2 chars in length
            && ctype_alpha($path[0]) // first char is alphabetic
            && $path[1] == ':' // second char is colon
        );
    }


    // Creates a "FilePath" instance with parameters representing this path type.

    public static function getFilePathInstance(string $path): FilePath
    {

        return new FilePath($path, self::SEPARATORS, self::DEFAULT_SEPARATOR, FilePath::NO_EMPTY_SEGMENTS);
    }
}
