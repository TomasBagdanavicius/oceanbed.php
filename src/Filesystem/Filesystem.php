<?php

declare(strict_types=1);

namespace LWP\Filesystem;

use LWP\Filesystem\FileType\File;
use LWP\Filesystem\FileType\Directory;
use LWP\Common\String\EnclosedCharsIterator;
use LWP\Filesystem\Exceptions\FileNotFoundException;
use LWP\Filesystem\Exceptions\FileReadError;
use LWP\Filesystem\Exceptions\FileWriteError;
use LWP\Filesystem\Exceptions\FileNotReadableException;
use LWP\Filesystem\Path\FilePath;
use LWP\Common\String\Format;
use LWP\Components\Datasets\Interfaces\DatabaseInterface;
use LWP\Filesystem\Path\PathEnvironmentRouter;
use LWP\Components\Datasets\Interfaces\DatasetInterface;
use LWP\Filesystem\Enums\FileTypeEnum;
use LWP\Filesystem\Interfaces\EditableFileInterface;
use LWP\Filesystem\Exceptions\DuplicateFileException;
use LWP\Common\Exceptions\ParseException;

class Filesystem
{
    public const DEFAULT_BASENAME_DUPLICATE_PATTERN = '{filename} copy[ %d].{extension}';


    //

    public static function exists(string $filename, string $error_msg_str = null, int $error_msg_code = 0): void
    {

        if (!$error_msg_str) {
            $error_msg_str = sprintf(
                "File %s was not found",
                $filename
            );
        }

        if (!file_exists($filename)) {
            throw new FileNotFoundException($error_msg_str, $error_msg_code);
        }
    }


    //

    public static function isReadable(string $filename, string $error_msg_str = null, int $error_msg_code = 0): void
    {

        if (!$error_msg_str) {
            $error_msg_str = sprintf("File \"%s\" is not readable.", $filename);
        }

        if (!is_readable($filename)) {
            throw new FileNotReadableException($error_msg_str, $error_msg_code);
        }
    }


    //

    public static function isWritable(string $filename, string $error_msg_str = null, int $error_msg_code = 0): void
    {

        if (!$error_msg_str) {
            $error_msg_str = sprintf("File \"%s\" is not writable.", $filename);
        }

        if (!is_writable($filename)) {
            throw new FileNotReadableException($error_msg_str, $error_msg_code);
        }
    }


    //

    public static function fileGetContents(string $filename, string $error_msg_str = null, int $error_msg_code = 0): string
    {

        self::exists($filename);
        self::isReadable($filename);

        if (!$error_msg_str) {
            $error_msg_str = sprintf("Could not read file \"%s\".", $filename);
        }

        if (($contents = file_get_contents($filename)) === false) {
            throw new FileReadError($error_msg_str, $error_msg_code);
        }

        return $contents;
    }


    //

    public static function filePutContents(string $filename, string $data, string $error_msg_str = null, int $error_msg_code = 0): int
    {

        if (!$error_msg_str) {
            $error_msg_str = sprintf("Could not write to file \"%s\".", $filename);
        }

        if (($write_len = file_put_contents($filename, $data)) === false) {
            throw new FileWriteError($error_msg_str, $error_msg_code);
        }

        return $write_len;
    }


    //

    public static function readByLineGenerator(string $filename): \Generator
    {

        self::exists($filename);
        self::isReadable($filename);

        $file_handle = self::filePointerOpen($filename);

        try {

            $line_number = 0;

            while (($line = fgets($file_handle)) !== false) {

                $line_number++;

                yield $line_number => trim($line);
            }

            // When you break the yield, this is required to close.
        } finally {

            fclose($file_handle);
        }
    }


    // Opens a file as file pointer resource. Doesn't take care of closing it.
    // @return (resource) - file pointer resource.

    public static function filePointerOpen(string $filename, string $mode = 'r')
    {

        if (!$file_handle = fopen($filename, $mode)) {
            throw new Exceptions\FileOpenError(sprintf("Could not open file \"%s\".", $filename));
        }

        return $file_handle;
    }


    //

    public static function arrayToJsonFile(array $data, FilePath $file_path): int
    {

        return self::filePutContents(
            $file_path->__toString(),
            json_encode($data, JSON_THROW_ON_ERROR)
        );
    }


    //

    public static function loadFileAndCall(string $filename, string $function_name, array $args = [])
    {

        self::exists($filename);

        // Using "require_once", because when called multipe times with the same filename, it can break.
        require_once $filename;

        if (!function_exists($function_name)) {
            return null;
        }

        try {
            return call_user_func_array($function_name, $args);
        } catch (\Throwable) {
            return null;
        }
    }


    //

    public static function generateUniqueFilename(
        string $path_prefix,
        string $path_suffix = '',
        int $iterator = 2,
        string $separator = ' '
    ): string {

        if ($iterator < 0) {
            throw new \ValueError("Parameter #3 (\$iterator) cannot be smaller than zero");
        }

        // First off, check if filename without iteration number exists.
        $filename = ($path_prefix . $path_suffix);

        while (file_exists($filename)) {

            $filename = ($path_prefix . $separator . $iterator . $path_suffix);
            $iterator++;
        }

        return $filename;
    }


    //

    public static function buildUniqueBasename(FilePath $file_path, string $template, int $iterator = 2): ?FilePath
    {

        if ($iterator < 0) {
            throw new \ValueError("Parameter #3 (\$iterator) cannot be smaller than zero");
        }

        $path = $file_path->getDirname();
        $data = [
            'filename' => $file_path->getFilename(),
            'extension' => $file_path->getExtension(),
        ];
        $enclosed_chars_iterator = new EnclosedCharsIterator($template, [
            '{' => ['}', true],
            '[' => [']', true],
        ], EnclosedCharsIterator::CURRENT_STRIPPED_OFF);
        $variants = [
            0 => '',
        ];
        $directory_separator = $file_path->getDefaultSeparator();

        foreach ($enclosed_chars_iterator as $substr) {

            if (!$enclosed_chars_iterator->hasEnclosingChars()) {

                $variants = array_map(fn ($value) => ($value . $substr), $variants);

            } else {

                $opening_char = $enclosed_chars_iterator->getOpeningChar();

                if ($opening_char === '{') {

                    $suffix = isset($data[$substr])
                        ? $data[$substr]
                        : '{' . $data[$substr] . '}';

                    foreach ($variants as $key => $variant) {
                        $variants[$key] .= $suffix;
                    }

                } else {

                    $variants[] = $variants[array_key_last($variants)] . $substr;
                }
            }
        }

        foreach ($variants as $basename_format) {

            $unique_pathname = null;
            $basename_candidate = sprintf($basename_format, $iterator);

            if ($basename_candidate !== $basename_format) {

                do {

                    $basename_candidate = sprintf($basename_format, $iterator);
                    $unique_pathname = ($path . $directory_separator . $basename_candidate);
                    $iterator++;

                } while (file_exists($unique_pathname));

            } else {

                if (!file_exists($path . $directory_separator . $basename_format)) {

                    $unique_pathname = ($path . $directory_separator . $basename_format);
                }
            }

            if ($unique_pathname) {
                break;
            }
        }

        return ($unique_pathname)
            ? $file_path->fromSelf($unique_pathname)
            : null;
    }


    //

    public static function convertTypeToEnumerated(string $file_type): ?FileTypeEnum
    {

        return match ($file_type) {
            'file' => FileTypeEnum::FILE,
            'directory' => FileTypeEnum::DIRECTORY,
            'link' => FileTypeEnum::LINK,
            default => null,
        };
    }


    //

    public static function createFile(string $pathname, FileTypeEnum $type, array $extra_params = []): EditableFileInterface
    {

        if (file_exists($pathname)) {
            throw new DuplicateFileException(sprintf(
                "File %s already exists",
                $pathname
            ));
        }

        $file_path = PathEnvironmentRouter::getStaticInstance()::getFilePathInstance($pathname);

        if ($type === FileTypeEnum::FILE) {
            return File::create($file_path, ...$extra_params);
        } elseif ($type === FileTypeEnum::DIRECTORY) {
            return Directory::create($file_path, ...$extra_params);
        }
    }


    //
    // @return "void" (as echo) or string.

    public static function loadFileAsTemplate(FilePath $template_path, bool $return = false, ?array $custom_vars = null)
    {

        ob_start();

        // Sandboxed template context.
        (static function () use ($template_path, $custom_vars): void {

            // Inject custom variables into the sandbox.
            if ($custom_vars) {

                foreach ($custom_vars as $var_name => $var_value) {

                    // Make sure not to override main variables, which are reserved.
                    if (!isset($$var_name)) {

                        // Builds a new variable.
                        ${$var_name} = $var_value;
                    }
                }
            }

            unset($custom_vars);

            // Catch, intercept, and redirect exceptions.
            try {

                include $template_path;

            } catch (\Throwable $exception) {

                throw new ParseException(
                    sprintf("Could not parse file template: %s", $exception->getMessage()),
                    previous: $exception
                );
            }

        })();

        $output = ob_get_contents();

        if ($output === false) {
            $output = '';
        }

        # Will shut down the output buffer.
        ob_end_clean();

        if (!$return) {
            echo $output;
        } else {
            return $output;
        }
    }
}
