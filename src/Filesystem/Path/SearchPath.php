<?php

declare(strict_types=1);

namespace LWP\Filesystem\Path;

use LWP\Common\String\Str;
use LWP\Filesystem\Path\Path;

class SearchPath implements \Stringable
{
    public const NO_EMPTY_SEGMENTS = 1;
    public const ALLOW_EMPTY_SEGMENTS = 2;
    public const RESOLVE_FULL = 1;
    public const RESOLVE_DOT_SEGMENTS = 2;

    private string $separators_string;
    private string $dirname;
    private string $dirname_raw;
    private $basename;
    private $filename;
    private $extension;
    private $leading_separators;
    private $trailing_separators;


    public function __construct(
        string $path,
        private array $separators,
        private string $default_separator,
        private int $segments_handling_mode = self::NO_EMPTY_SEGMENTS,
    ) {

        $this->separators_string = implode($separators);

        $this->parse($path);
    }


    // Builds the resulting path.

    public function __toString(): string
    {

        $result = '';

        if (isset($this->dirname)) {
            $result .= $this->dirname;
        }

        $result .= $this->leading_separators . $this->basename . $this->trailing_separators;

        return $result;
    }


    // Gets separators.

    public function getSeparators(): array
    {

        return $this->separators;
    }


    // Gets default separator.

    public function getDefaultSeparator(): string
    {

        return $this->default_separator;
    }


    //

    public function getSegmentsHandlingMode(): int
    {

        return $this->segments_handling_mode;
    }


    // Parses the path and registers all derived path components.

    private function parse(string $path): void
    {

        $trimmed = ($this->segments_handling_mode == self::NO_EMPTY_SEGMENTS)
            ? rtrim($path, $this->separators_string)
            : $path;

        $this->trailing_separators = substr($path, strlen($trimmed));

        if (!empty($trimmed)) {

            $pos = -1;

            foreach ($this->separators as $separator) {

                $current_pos = strrpos($trimmed, $separator);

                if ($current_pos > $pos) {
                    $pos = $current_pos;
                }
            }

            if ($pos >= 0) {

                $this->basename = substr($trimmed, ($pos + 1));
                $dirname = substr($trimmed, 0, ($pos + 1));

                $this->dirname = rtrim($dirname, $this->separators_string);
                $this->dirname_raw = $dirname;
                $this->leading_separators = substr($dirname, strlen($this->dirname));

            } else {

                $this->dirname = '';
                $this->basename = $trimmed;
                $this->leading_separators = '';
            }

            $this->setBasename($this->basename);

        } else {

            $this->basename = $this->filename = $this->extension = $this->trailing_separators = '';
        }
    }


    // Appends a new segment to the path.

    public function appendSegment(string $segment): self
    {

        if ($segment !== '') {

            foreach ($this->separators as $separator) {

                $pos = strpos($segment, $separator);

                if ($pos !== false) {
                    throw new \Exception(sprintf(
                        "Segment \"%s\" cannot contain a separator character at position \"%d\".",
                        $segment,
                        $pos
                    ));
                }
            }
        }

        $this->dirname .= ($this->leading_separators . $this->basename);
        $this->trailing_separators = '';

        $this->setBasename($segment);

        return $this;
    }


    // Removes the last segment from the path.

    public function popSegment(): self
    {

        $parts = $this->parse($this->dirname);

        return $this;
    }


    // Gets the dirname.

    public function getDirname(bool $raw = false): string
    {

        return ((
            (!$raw)
            ? $this->dirname
            : $this->dirname_raw
        ) ?: Path::CURRENT_DIR_SYMBOL);
    }


    // Gets the basename.

    public function getBasename(): string
    {

        return $this->basename;
    }


    // Sets a new basename and other components that are derived from it.

    public function setBasename(string $basename): self
    {

        if ($basename == '' && $this->segments_handling_mode == self::NO_EMPTY_SEGMENTS) {
            throw new \Exception("Segment cannot be empty.");
        }

        $basename_parts = Path::parseBasename($basename);

        $this->basename = $basename;
        $this->filename = $basename_parts['filename'];

        $this->extension = (isset($basename_parts['extension']))
            ? $basename_parts['extension']
            : '';

        return $this;
    }


    // Gets extension.

    public function getExtension(bool $include_prefix = false): string
    {

        return (!$include_prefix)
            ? $this->extension
            : (Path::FILENAME_EXTENSION_PREFIX . $this->extension);
    }


    // Sets new extension.

    public function setExtension(string $extension): void
    {

        $this->basename = ($this->filename . Path::FILENAME_EXTENSION_PREFIX . $extension);
        $this->extension = $extension;
    }


    // Gets the filename.

    public function getFilename(): string
    {

        return $this->filename;
    }


    // Sets new filename.

    public function setFilename(string $filename): void
    {

        $this->basename = ($filename . Path::FILENAME_EXTENSION_PREFIX . $this->extension);
        $this->filename = $filename;
    }


    // Compresses the path by performing normalization routine.

    public function compress(int $mode = self::RESOLVE_FULL, bool $preserve_leading_dot_segments = true): void
    {

        $this->parse(self::normalize($this->__toString(), $this->separators, $this->default_separator, $mode, $preserve_leading_dot_segments));
    }


    // Get static path instance.

    public function getStaticPathInstance(): Path
    {

        return (PathEnvironmentRouter::matchesDefaultWindowsPathSeparator($this->default_separator))
            ? PathEnvironmentRouter::getWindowsPathStaticInstance()
            : PathEnvironmentRouter::getPosixPathStaticInstance();
    }


    // Normalizes a given path.

    public static function normalize(
        string $path,
        array $separators,
        string $default_separator,
        int $mode = self::RESOLVE_FULL,
        bool $preserve_leading_dot_segments = true
    ): string {

        /* When the path is empty, "." is returned, representing the current working directory.
        This is one of the rare scenarios where the resulting path contains dot segments, even
        though full normalization is considered to be completed. */
        if ($path === '') {
            return Path::CURRENT_DIR_SYMBOL;
        }

        $result = '';

        if (in_array($path[0], $separators)) {

            $result .= $default_separator;
            $path = substr($path, 1);
        }

        $result_parts = [];
        $found_non_dot_segment = 0;
        $leading_double_dot_segments = 0;

        Str::splitMultiple($path, $separators, function (string $segment, int $position) use (
            $path,
            $mode,
            $separators,
            $default_separator,
            $preserve_leading_dot_segments,
            &$result_parts,
            &$found_non_dot_segment,
            &$leading_double_dot_segments,
        ): void {

            if ($segment == Path::PARENT_DIR_SYMBOL) {

                if (!empty($result_parts) && $found_non_dot_segment) {

                    array_pop($result_parts);
                    $found_non_dot_segment--;

                } elseif ($preserve_leading_dot_segments) {

                    $part = Path::PARENT_DIR_SYMBOL;

                    if ($position >= 0 && in_array($path[$position], $separators)) {
                        $part .= $default_separator;
                    }

                    $result_parts[] = $part;

                } elseif (!$found_non_dot_segment) {

                    $leading_double_dot_segments++;
                }

            } elseif ($segment == '') {

                if ($mode == self::RESOLVE_DOT_SEGMENTS) {

                    $found_non_dot_segment++;

                    if ($position >= 0 && in_array($path[$position], $separators)) {
                        $result_parts[] = $default_separator;
                    }
                }

            } elseif ($segment != Path::CURRENT_DIR_SYMBOL) {

                $part = $segment;

                if ($position >= 0 && in_array($path[$position], $separators)) {
                    $part .= $default_separator;
                }

                $result_parts[] = $part;

                $found_non_dot_segment++;
            }

        });

        $join = implode($result_parts);

        /* This is used to emphasize that an empty segment is at the begining of the resulting path.
        The "./" is prepended when empty segments are allowed and when there were leading double dot
        segments ignored. For instance, instead of returning /foo/bar, it would return .//foo/bar.
        The latter clearly preserves the remaining empty segment. However, this is not very important,
        because first of all this scenario is working when leading dots are not preserved, and secondly
        theoretically the empty segment would be restored by joining in the path prefix, eg. /root/path/ +
        /foo/bar would result in /root/path//foo/bar. Similarly to /root/path/.//foo/bar. */
        if ($mode == self::RESOLVE_DOT_SEGMENTS && !$preserve_leading_dot_segments && $leading_double_dot_segments && $result == '' && $join != '' && $join[0] == $default_separator) {
            $result = (Path::CURRENT_DIR_SYMBOL . $default_separator);
        }

        return ($result . implode($result_parts));
    }
}
