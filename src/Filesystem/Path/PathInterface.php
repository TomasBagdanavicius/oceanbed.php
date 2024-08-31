<?php

declare(strict_types=1);

namespace LWP\Filesystem\Path;

interface PathInterface
{
    // To tell if a path is absolute.

    public static function isAbsolute(string $path): bool;


    // To split path into root and search path components.

    public static function splitAtRoot(string $path): array;


    // To create a "FilePath" instance with parameters representing that path type.

    public static function getFilePathInstance(string $path): FilePath;
}
