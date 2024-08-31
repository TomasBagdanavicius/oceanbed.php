<?php

declare(strict_types=1);

namespace LWP\Filesystem\Path;

/* Some of the static methods in LWP\Filesystem\Path\Path are protected,
because they require static parameters (such as separators) from the child
class. This trait class sets up routing to those methods. */
trait PathChildCaller
{
    // Parse using current class'es static parameters.

    public static function parse(string $path): array
    {

        return parent::parse($path);
    }


    // Normalize using current class'es static parameters.

    public static function normalize(string $path): string
    {

        return parent::normalize($path);
    }


    // Resolve using current class'es static parameters.

    public static function resolve(): string
    {

        return call_user_func_array([parent::class, 'resolve'], func_get_args());
    }


    // Joins paths using current class'es static parameters.

    public static function join(): string
    {

        return call_user_func_array([parent::class, 'join'], func_get_args());
    }


    // Splits the given path into segments.
    /* This function needs to be compatible with Path::split() and inevitably "separators" cannot be avoided.
    To cope with that the function injects individual class separators, when no custom separators are given. */

    public static function split(string $path, array $separators = [], int $pos = 0, bool $add_indices = true): array
    {

        $args = func_get_args();

        // This is the desired input for the separators.
        if (!$separators) {
            $args[1] = static::SEPARATORS;
        }

        return call_user_func_array([parent::class, 'split'], $args);
    }


    // Builds relative path using current class'es static parameters.

    public static function relative(string $from, string $to, string $current_dir = __DIR__): string
    {

        return parent::relative($from, $to, $current_dir);
    }
}
