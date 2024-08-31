<?php

declare(strict_types=1);

namespace LWP\Filesystem\Iterators;

use LWP\Filesystem\FileType\Directory;
use LWP\Filesystem\Path\PathEnvironmentRouter;
use LWP\Filesystem\FileType\File;
// "Directory" fails with "the name is already in use" in "RecursiveDirectoryIterator" context.
use LWP\Filesystem\FileType\Directory as Dir;

class MyRecursiveDirectoryIterator extends \RecursiveDirectoryIterator
{
    public function __construct(
        protected Dir $directory,
        protected int $flags = \FilesystemIterator::KEY_AS_PATHNAME | \FilesystemIterator::CURRENT_AS_FILEINFO | \FilesystemIterator::SKIP_DOTS
    ) {

        parent::__construct(
            $directory->pathname,
            $flags
        );
    }


    // Returns the directory object.

    public function getDirectory(): Directory
    {

        return $this->directory;
    }


    //

    public function getChildren(): \RecursiveDirectoryIterator
    {

        $path = PathEnvironmentRouter::getStaticInstance();
        $file_path = $path::getFilePathInstance($this->getPathname());

        return new self(
            new Dir($file_path),
        );
    }


    //

    public function current(): \SplFileInfo
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
                : Dir::class;

            return new $class_name(...$params);
        }

        return parent::current();
    }
}
