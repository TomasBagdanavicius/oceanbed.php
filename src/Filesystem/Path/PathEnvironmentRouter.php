<?php

declare(strict_types=1);

namespace LWP\Filesystem\Path;

class PathEnvironmentRouter
{
    // Get static path object instance for the current working environment.

    public static function getStaticInstance(): Path
    {

        return (self::isWindows())
            ? self::getWindowsPathStaticInstance()
            : self::getPosixPathStaticInstance();
    }


    // Gets static "WindowsPath" instance without invoking the constructor.

    public static function getWindowsPathStaticInstance(): WindowsPath
    {

        return self::getInstanceByClassName(__NAMESPACE__ . '\WindowsPath');
    }


    // Gets static "PosixPath" instance without invoking the constructor.

    public static function getPosixPathStaticInstance(): PosixPath
    {

        return self::getInstanceByClassName(__NAMESPACE__ . '\PosixPath');
    }


    // Gets static chosen class instance without invoking the constructor.

    public static function getInstanceByClassName(string $class_name): Path
    {

        $reflection = new \ReflectionClass($class_name);

        return $reflection->newInstanceWithoutConstructor();
    }


    // Tells if current working environment is Windows.

    public static function isWindows(): bool
    {

        return self::matchesDefaultWindowsPathSeparator(DIRECTORY_SEPARATOR);
    }


    // Tells if given separator matches the default Windows path separator.

    public static function matchesDefaultWindowsPathSeparator(string $separator): bool
    {

        return ($separator == WindowsPath::DEFAULT_SEPARATOR);
    }


    // Tells if current working environment is POSIX.

    public static function isPosix(): bool
    {

        return self::matchesDefaultPosixPathSeparator(DIRECTORY_SEPARATOR);
    }


    // Tells if given separator matches the default Posix path separator.

    public static function matchesDefaultPosixPathSeparator(string $separator): bool
    {

        return ($separator == PosixPath::DEFAULT_SEPARATOR);
    }
}
