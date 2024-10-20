<?php

declare(strict_types=1);

namespace LWP\Filesystem\FileType;

use LWP\Common\Indexable;
use LWP\Common\Collectable;
use LWP\Common\Conditions\ConditionMatchableInterface;
use LWP\Common\Interfaces\WithProperties;
use LWP\Components\Datasets\Interfaces\DatabaseInterface;
use LWP\Components\Datasets\Interfaces\DatasetInterface;
use LWP\Components\Datasets\AbstractDatasetSelectHandle;
use LWP\Filesystem\Path\FilePath;
use LWP\Filesystem\Filesystem;
use LWP\Filesystem\FilesystemStats;
use LWP\Filesystem\Exceptions\FileIsNotADirectoryException;
use LWP\Filesystem\Exceptions\FileNotFoundException;
use LWP\Filesystem\Interfaces\EditableFileInterface;
use LWP\Filesystem\Enums\FileActionEnum;
use LWP\Filesystem\Enums\FileTypeEnum;
use LWP\Filesystem\Exceptions\FileDeleteError;
use LWP\Filesystem\Exceptions\FileTruncateError;
use LWP\Filesystem\Exceptions\DirectoryCreateError;
use LWP\Filesystem\FileCollection;
use LWP\Filesystem\FilesystemDatasetSelectHandle;
use LWP\Filesystem\Enums\FilesystemStatsStatusEnum;
use LWP\Filesystem\FileType\FileFormats\FileZip;

class Directory extends \SplFileInfo implements
    EditableFileInterface,
    Indexable,
    Collectable,
    ConditionMatchableInterface,
    \Countable
{
    use EditableFileTrait;
    use \LWP\Common\IndexableTrait;
    use \LWP\Common\CollectableTrait;

    public readonly string $pathname;


    public function __construct(
        public readonly FilePath $file_path,
        public readonly ?FilesystemStats $filesystem_stats = null,
    ) {

        $this->pathname = $file_path->__toString();
        // SplFileInfo will not check if file exists
        Filesystem::exists($this->pathname);

        if (!is_dir($this->pathname)) {
            throw new FileIsNotADirectoryException(sprintf(
                "File \"%s\" is not a directory",
                $this->pathname
            ));
        }

        // SplFileInfo will not check if file exists
        parent::__construct($this->pathname);
    }


    //

    public function getType(): string
    {

        return 'directory';
    }


    //

    public function getTypeEnumerated(): FileTypeEnum
    {

        return FileTypeEnum::DIRECTORY;
    }


    // Gets directory's full size (including size of all nested items)
    // Does not add size of nested directories

    public function getRealSize(): int|false
    {

        $size = 0;

        $this->getReader([DirectoryReader::RECURSE])->forEach(function (\SplFileInfo $file) use (&$size): void {
            if ($file->isFile()) {
                $size += $file->getSize();
            }
        });

        return $size;
    }


    // Gets the number of items inside the directory (including nested directories)

    public function count(): int
    {

        $count = 0;

        $this->getReader([DirectoryReader::RECURSE])->forEach(function (\SplFileInfo $file) use (&$count): void {
            $count++;
        });

        return $count;
    }


    // Returns the reader that can read through the files in this directory.

    public function getReader(array $params = []): DirectoryReader
    {

        return new DirectoryReader($this, ...$params);
    }


    // Checks whether it contains a given file directly inside the current directory.

    public function contains(string $file_name): bool
    {

        return file_exists(
            $this->file_path
            . $this->file_path->getDefaultSeparator()
            . $file_name
        );
    }


    //

    public function changeToInside(string $dir_name): self
    {

        // This gets referenced outside this object.
        $file_path_copy = clone $this->file_path;
        $search_file_path = $file_path_copy->appendSegment($dir_name);

        if (!$search_file_path->existsInFilesystem()) {
            throw new FileNotFoundException(sprintf(
                "File \"%s\" was not found",
                $search_file_path->__toString()
            ));
        }

        return new self($search_file_path, $this->filesystem_stats);
    }


    // Creates a new directory in the filesystem recursively, registers all system activities, returns an instance of the "Directory" object.

    public static function create(
        FilePath $file_path,
        int $permissions = 0777,
        // By reference, because on error it will throw an exception and there will be no way to return filesystem stats
        ?FilesystemStats &$filesystem_stats = null,
    ): self {

        $pathname = $file_path->__toString();

        // Directory exists
        if ($file_path->existsInFilesystem()) {

            if ($filesystem_stats) {
                $filesystem_stats->registerFound(FileActionEnum::CREATE, $pathname);
            }

            return new self($file_path, $filesystem_stats);
        }

        $path = $file_path->getStaticPathInstance();

        // An absolute path was provided, therefore we need to split it at root
        if ($path::isAbsolute($pathname)) {

            $split_at_root = $path::splitAtRoot($pathname);
            $root_path = $split_at_root['root'];
            $relative_path = $split_at_root['search_path']->__toString();

            // Relative path provided
        } else {

            $root_path = '';
            $relative_path = $pathname;
        }

        $relative_path_parts = $path::split($relative_path);
        $build_path = $root_path;
        $first_created = false;
        $chain_fail = false;
        $i = 0;
        $create_count = 0;

        // Run through all segments in the relative path
        foreach ($relative_path_parts as $part_data) {

            if ($i) {
                $build_path .= $path::DEFAULT_SEPARATOR;
            }

            $build_path .= $part_data[1];

            if (!$chain_fail) {

                if ($first_created || !file_exists($build_path)) {

                    if (!mkdir($build_path, $permissions, recursive: false)) {

                        $chain_fail = true;

                        if ($filesystem_stats) {
                            $filesystem_stats->registerFailure(FileActionEnum::CREATE, $build_path);
                        }

                    } else {

                        $first_created = true;

                        if ($filesystem_stats) {
                            $filesystem_stats->registerSuccess(FileActionEnum::CREATE, $build_path);
                        }

                        $create_count++;
                    }

                    // Found: will store all found directories starting from root path
                } elseif ($filesystem_stats) {

                    $filesystem_stats->registerFound(FileActionEnum::CREATE, $build_path);
                }

                // Registers failure for each individual directory that was not created
            } elseif ($filesystem_stats) {

                $filesystem_stats->registerFailure(FileActionEnum::CREATE, $build_path);
            }

            $i++;
        }

        if ($chain_fail) {
            throw new DirectoryCreateError(sprintf(
                "Could not create directory %s",
                $build_path
            ));
        }

        return new self(
            new FilePath(realpath($build_path), $path::SEPARATORS, $path::DEFAULT_SEPARATOR),
            $filesystem_stats
        );
    }


    // Removes the file (not to be confused with deletion)

    public function remove(): true
    {

        $result = rmdir($this->pathname);

        if (!$result) {
            throw new FileDeleteError(sprintf(
                "Could not remove %s",
                $this->pathname
            ));
        }

        return true;
    }


    //

    public function truncate(): self
    {

        $success_count = 0;
        $failure_count = 0;
        $reader_params = [
            DirectoryReader::RECURSE | DirectoryReader::CHILD_FIRST
        ];

        $this->getReader($reader_params)->forEach(
            function (
                \SplFileInfo $file
            ) use (
                &$success_count,
                &$failure_count,
            ): void {

                try {
                    $file->remove();
                    $success_count++;
                } catch (FileDeleteError) {
                    $failure_count++;
                }
            }
        );

        if ($failure_count) {

            throw new FileTruncateError(sprintf(
                "Could not truncate directory %s",
                $this->pathname
            ));
        }

        return $this;
    }


    // Deletes the directory

    public function delete(): true
    {

        try {

            $this->truncate();
            // Remove self
            $this->remove();

        } catch (FileTruncateError|FileDeleteError $exception) {

            throw new FileDeleteError(sprintf(
                "Could not delete directory %s",
                $this->pathname,
                previous: $exception
            ));
        }

        return true;
    }


    //

    public function duplicate(string $suffix = ' copy'): self
    {


    }


    //

    public function isEmpty(): bool
    {

        foreach ($this->getReader() as $file) {
            return false;
        }

        return true;
    }


    //

    public function zip(): FileZip
    {


    }


    // Gets collection of all files inside this directory

    public function getFileCollection(): FileCollection
    {


    }


    // Gets all files selected inside this directory

    public function selectAll(): FileSelection
    {


    }
}
