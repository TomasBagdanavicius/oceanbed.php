<?php

declare(strict_types=1);

namespace LWP\Filesystem\Path;

use LWP\Common\String\Str;

abstract class Path
{
    public const CURRENT_DIR_SYMBOL = '.';
    public const PARENT_DIR_SYMBOL = '..';
    public const FILENAME_EXTENSION_PREFIX = '.';

    public const SEPARATORS = ['/'];
    public const DEFAULT_SEPARATOR = '/';


    // Statically parse a path into its components - root, dirname, basename, filename, extension.
    /* This function was assembled to have a way to parse a path without applying any resolution process
    similarly to what one would get from pathinfo(). However, this function additionally extracts the
    rather important "root" component. */

    protected static function parse(string $path): array
    {

        $result = static::splitAtRoot($path);

        $dirname = $result['search_path']->getDirname();
        $result['dirname'] = ($result['root'] . (($dirname != self::CURRENT_DIR_SYMBOL) ? $dirname : ''));

        return $result;
    }


    // Normalizes the given path.

    protected static function normalize(string $path): string
    {

        $main_parts = static::splitAtRoot($path);

        $main_parts['root'] = str_replace(static::SEPARATORS, static::DEFAULT_SEPARATOR, $main_parts['root']);

        $main_parts['search_path']->compress(static::PATH_RESOLUTION_MODE, ($main_parts['root'] == ''));

        return ($main_parts['root'] . $main_parts['search_path']->__toString());
    }


    // Resolves a sequence of paths or path segments into a single path.

    protected static function resolve(): string
    {

        $result = [];
        $paths = func_get_args();
        $numargs = func_num_args();

        for ($i = ($numargs - 1); $i >= 0; $i--) {

            $path = $paths[$i];

            if (!is_string($path)) {
                throw new \TypeError("All path arguments must be of string type.");
            } elseif ($path == '') {
                continue;
            }

            array_unshift($result, $path);

            if (static::isAbsolute($path)) {
                break;
            }
        }

        return SearchPath::normalize(implode(static::DEFAULT_SEPARATOR, $result), static::SEPARATORS, static::DEFAULT_SEPARATOR, SearchPath::RESOLVE_FULL);
    }


    // Joins given path segments using environment specific separator, then normalizes the resulting path.

    protected static function join(): string
    {

        $paths = array_filter(func_get_args(), function (mixed $value): bool {

            if (!is_string($value)) {
                throw new \TypeError("All path arguments must be of string type");
            }

            return ($value !== '');

        });

        return (!empty($paths))
            ? SearchPath::normalize(implode(static::DEFAULT_SEPARATOR, $paths), static::SEPARATORS, static::DEFAULT_SEPARATOR, SearchPath::RESOLVE_FULL)
            : self::CURRENT_DIR_SYMBOL;
    }


    // Builds a relative path from one path to the other. If any of these paths is relative, it will be joined with the current directory path.

    protected static function relative(string $from, string $to, string $current_dir = __DIR__): string
    {

        $from = (!static::isAbsolute($from))
            ? self::join($current_dir, $from)
            : SearchPath::normalize($from, static::SEPARATORS, static::DEFAULT_SEPARATOR, SearchPath::RESOLVE_FULL);

        $to = (!static::isAbsolute($to))
            ? self::join($current_dir, $to)
            : SearchPath::normalize($to, static::SEPARATORS, static::DEFAULT_SEPARATOR, SearchPath::RESOLVE_FULL);

        $from = trim($from, implode(static::SEPARATORS));
        $to = trim($to, implode(static::SEPARATORS));

        $from_parts = Str::splitMultiple($from, static::SEPARATORS);
        $to_parts = Str::splitMultiple($to, static::SEPARATORS);

        $from_parts_count = count($from_parts);
        $count_matching_leading_dirs = 0;

        for ($i = 0; $i < $from_parts_count; $i++) {

            if (isset($to_parts[$i]) && $from_parts[$i] == $to_parts[$i]) {
                $count_matching_leading_dirs++;
            } else {
                break;
            }
        }

        $parts = array_merge(
            array_fill(0, ($from_parts_count - $count_matching_leading_dirs), self::PARENT_DIR_SYMBOL),
            array_slice($to_parts, $count_matching_leading_dirs)
        );

        return implode(static::DEFAULT_SEPARATOR, $parts);
    }


    // Splits a path into segments and provides additional information about position of segments and separators.
    // $add_indices - Whether to add additional info to the result elements, eg. position data.

    protected static function split(string $path, array $separators = [], int $pos = 0, bool $add_indices = true): array
    {

        $parts = [];
        $len = strlen($path);
        $path = ltrim($path, implode($separators));

        if ($path === '') {
            return $parts;
        }

        if (($trimmed_len = strlen($path)) < $len) {
            $pos += ($len - $trimmed_len);
        }

        do {

            $start = $pos;
            $positions = Str::posMultiple($path, $separators);

            if ($has_separators = !empty($positions)) {

                $next_pos = min($positions);
                $pos += $next_pos;
                $label = substr($path, 0, $next_pos);
                $path = substr($path, $next_pos);

                if ($add_indices) {
                    $label_end = ($pos - 1);
                }

                $len = strlen($path);
                $path = ltrim($path, implode($separators));

                if (($trimmed_len = strlen($path)) < $len) {
                    $pos += ($len - $trimmed_len);
                }

            } else {

                $label = $path;
                $pos += strlen($label);

                if ($add_indices) {
                    $label_end = ($pos - 1);
                }
            }

            if (empty($label)) {
                break;
            }

            if ($add_indices) {

                $parts[] = [
                    // Position of first label's char.
                    $start,
                    // Label string.
                    $label,
                    // Position of last char in the label.
                    $label_end,
                    // Position of endmost slash.
                    ($pos - 1),
                ];

            } else {

                $parts[] = $label;
            }

        } while ($has_separators);

        return $parts;
    }


    // Parses a basename string into filename and extension components.

    public static function parseBasename(string $basename): array
    {

        $prefix = self::FILENAME_EXTENSION_PREFIX;

        if ($basename === '') {

            return [
                'filename' => '',
                'extension' => '',
            ];
        }

        if (
            // Current directory file name
            $basename === self::CURRENT_DIR_SYMBOL
            // Parent directory file name
            || $basename === self::PARENT_DIR_SYMBOL
            // Starts with a dot and contains just that single dot
            || ($basename[0] === $prefix && strpos($basename, $prefix, 1) === false)
            // No dots at all
            || ($pos = strrpos($basename, $prefix)) === false
        ) {

            return [
                'filename' => $basename,
                'extension' => '',
            ];

        } else {

            return [
                'filename' => substr($basename, 0, $pos),
                'extension' => substr($basename, ($pos + 1)),
            ];
        }
    }


    // Builds basename string.

    public static function buildBasename(string $filename, string $extension = ''): string
    {

        $basename = $filename;

        if ($extension) {
            $basename .= (self::FILENAME_EXTENSION_PREFIX . $extension);
        }

        return $basename;
    }


    // Removes all trailing separators.
    // This method doesn't take into account the root element.

    public static function rtrim(string $path): string
    {

        return rtrim($path, implode(static::SEPARATORS));
    }
}
