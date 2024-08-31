<?php

declare(strict_types=1);

namespace LWP;

// Sets internal character encoding for multi-byte functions
mb_internal_encoding("UTF-8");


class Autoload
{
    public const EXTENSION = '.php';


    // Registers "__autoload()" implementation function.

    public static function register(): bool
    {

        define('OCEANBED_AUTOLOAD_DIRNAME', __DIR__);

        spl_autoload_extensions(self::EXTENSION);

        return spl_autoload_register([
            self::class,
            'loadFileByNamespaceName',
        ]);
    }


    // Converts a namespace name to filename.

    public static function convertNamespaceNameToFilename(
        string $namespace_name
    ): ?string {

        $namespace_parts = explode('\\', $namespace_name, 2);

        // Check that first level label (aka vendor name) in the namespace
        // hierarchy matches the app prefix.
        return ($namespace_parts[0] === __NAMESPACE__)
            ? (__DIR__
                . DIRECTORY_SEPARATOR
                . str_replace('\\', DIRECTORY_SEPARATOR, $namespace_parts[1])
                . self::EXTENSION)
            : null;
    }


    // Converts path name to namespace name.

    public static function convertPathnameToNamespaceName(string $pathname): ?string
    {

        if (!file_exists($pathname) || !str_starts_with($pathname, __DIR__) || !str_ends_with($pathname, self::EXTENSION)) {
            return null;
        }

        $suffix = substr($pathname, strlen(__DIR__), -strlen(self::EXTENSION));

        return ('\\' . __NAMESPACE__ . str_replace('/', '\\', $suffix));
    }


    // Loads a file by a given namespace name.

    public static function loadFileByNamespaceName(
        string $namespace_name,
        bool $include = true
    ): void {

        $pathname = self::convertNamespaceNameToFilename($namespace_name);

        if ($pathname) {
            self::loadFile($pathname, $include);
        }
    }


    // Loads a file.

    public static function loadFile(string $pathname, bool $include = true): void
    {

        if (file_exists($pathname)) {

            if ($include) {
                include $pathname;
            } else {
                require_once $pathname;
            }

        } else {

            $message = "Could not autoload $pathname: file not found";
            $trace = (new \Exception())->getTrace();

            if (isset($trace[1]['file'])) {
                $message .= sprintf(", came from: %s", $trace[1]['file']);
            }

            // Will not use `\LWP\Filesystem\Exceptions\FileNotFoundException`
            // in order to have a vanilla solution
            throw new \RuntimeException($message);
        }
    }
}

Autoload::register();
