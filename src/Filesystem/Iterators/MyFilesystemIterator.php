<?php

declare(strict_types=1);

namespace LWP\Filesystem\Iterators;

use LWP\Filesystem\FileType\File;
use LWP\Filesystem\FileType\Directory;
use LWP\Filesystem\Path\PathEnvironmentRouter;

class MyFilesystemIterator extends \FilesystemIterator
{
    public const KEY_AS_RELATIVE_PATHNAME = 1;


    public function __construct(
        protected Directory $directory,
        protected int $flags = \FilesystemIterator::KEY_AS_PATHNAME | \FilesystemIterator::CURRENT_AS_FILEINFO | \FilesystemIterator::SKIP_DOTS
    ) {

        parent::__construct(
            $directory->pathname,
            $flags
        );
    }


    //

    public static function getDefaultFlags(): int
    {

        return \FilesystemIterator::KEY_AS_PATHNAME | \FilesystemIterator::CURRENT_AS_FILEINFO | \FilesystemIterator::SKIP_DOTS;
    }


    // Returns the directory object.

    public function getDirectory(): Directory
    {

        return $this->directory;
    }


    //

    public function key(): string
    {

        if ($this->flags & self::KEY_AS_RELATIVE_PATHNAME) {

            $current_element = parent::current();

            $relative_pathname = substr(
                $current_element->getPathname(),
                // +1 to strip off directory separator
                (strlen($this->directory->pathname) + 1)
            );

            return $relative_pathname;
        }

        return parent::key();
    }


    //

    public function current(): string|\SplFileInfo|\FilesystemIterator
    {

        $is_file = parent::isFile();
        $is_directory = parent::isDir();

        if ($is_file || $is_directory) {

            $current = parent::current();
            $filepath = (is_string($current))
                ? $current
                : parent::getPathname();
            $path = PathEnvironmentRouter::getStaticInstance();
            $params = [
                // File path.
                $path::getFilePathInstance($filepath),
            ];
            $class_name = ($is_file)
                ? File::class
                : Directory::class;

            return new $class_name(...$params);
        }

        return parent::current();
    }
}
