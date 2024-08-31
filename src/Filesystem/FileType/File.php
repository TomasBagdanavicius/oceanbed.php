<?php

declare(strict_types=1);

namespace LWP\Filesystem\FileType;

use LWP\Common\Indexable;
use LWP\Common\Collectable;
use LWP\Filesystem\Path\FilePath;
use LWP\Filesystem\FilesystemStats;
use LWP\Filesystem\Filesystem;
use LWP\Filesystem\Interfaces\EditableFileInterface;
use LWP\Filesystem\Enums\FileActionEnum;
use LWP\Common\Conditions\Condition;
use LWP\Common\Conditions\ConditionGroup;
use LWP\Common\Conditions\ConditionMatchableInterface;
use LWP\Common\Interfaces\Sizeable;
use LWP\Filesystem\Exceptions\DuplicateFileException;
use LWP\Filesystem\Exceptions\FileCreateError;
use LWP\Filesystem\Exceptions\FileDeleteError;
use LWP\Filesystem\Exceptions\FileTruncateError;
use LWP\Filesystem\Exceptions\FileWriteError;
use LWP\Filesystem\Path\Path;
use LWP\Filesystem\Enums\FileTypeEnum;
use LWP\Filesystem\Enums\DuplicateHandlingOptionsEnum;
use LWP\Filesystem\Exceptions\FileCopyError;
use LWP\Filesystem\Exceptions\FileMoveError;
use LWP\Filesystem\Enums\RelocateMethodEnum;
use LWP\Filesystem\Exceptions\FileNotFoundException;
use LWP\Filesystem\Enums\FilesystemStatsStatusEnum;
use LWP\Filesystem\FileType\FileFormats\FileZip;

class File extends \SplFileObject implements
    EditableFileInterface,
    Indexable,
    Collectable,
    ConditionMatchableInterface,
    Sizeable,
    \Countable
{
    use EditableFileTrait {
        rename as traitRename;
    }
    use \LWP\Common\IndexableTrait;
    use \LWP\Common\CollectableTrait;

    public const KEY_AS_LINE_NUMBER = 1;

    public readonly string $pathname;
    private FilePath $file_path;


    public function __construct(
        FilePath $file_path,
        public readonly ?FilesystemStats $filesystem_stats = null,
        /* Tried using extra bitwise constants in addition to the existing 4,
        but when I add value "16" it's not picked up by getFlags(). */
        protected int $flags = self::KEY_AS_LINE_NUMBER
    ) {

        $this->pathname = $file_path->__toString();
        Filesystem::exists($this->pathname);
        $this->file_path = $file_path;

        parent::__construct(
            filename: $this->pathname,
            mode: 'c+',
        );
    }


    // Gets enumerated file type

    public function getTypeEnumerated(): FileTypeEnum
    {

        return FileTypeEnum::FILE;
    }


    //

    public function getFilename(): string
    {

        return $this->getBasename(Path::FILENAME_EXTENSION_PREFIX . $this->getExtension());
    }


    //
    /* This method overrides the build-in one, because of how the latter is
    handling dot files, eg. for a ".config" pathname it returns "config", when
    the more conventional way would be to return empty string. */

    public function getExtension(): string
    {

        $basename = $this->getBasename();
        $basename_components = Path::parseBasename($basename);

        return $basename_components['extension'];
    }


    // Returns the number of lines in the file

    public function count(): int
    {

        $count_lines = 0;
        $this->rewind();

        while (!$this->eof()) {
            // Can return an empty string, which is valid (eg. last line)
            $fgets = $this->fgets();
            $count_lines++;
        }

        return $count_lines;
    }


    // An alias of the `count()` method

    public function countLines(): int
    {

        return $this->count();
    }


    // Gets current line number

    public function key(): int
    {

        if ($this->flags & self::KEY_AS_LINE_NUMBER) {
            return (parent::key() + 1);
        }

        return parent::key();
    }


    // An alias of the `getSize()` method

    public function getRealSize(): int|false
    {

        return $this->getSize();
    }


    // Loads full contents into a string

    public function getContents(): string|false
    {

        return file_get_contents($this->pathname);
    }


    // Replaces contents with the given one

    public function putContents(string $contents): self
    {

        $result = file_put_contents($this->pathname);

        if (!$result) {
            throw new FileWriteError(sprintf(
                "Could not write to file %s",
                $this->pathname
            ));
        }

        return $this;
    }


    // Puts a new line with a new line break at the end of the string

    public function putLine(string $line, bool $add_carriage_return = false): self
    {

        $new_line_char = "\n";

        if ($add_carriage_return) {
            $new_line_char = "\r\n";
        }

        $line = ($line . $new_line_char);
        $write_len = $this->fwrite($line);

        if ($write_len === false || $write_len !== strlen($line)) {
            throw new FileWriteError(sprintf(
                "Could not write a line to file %s",
                $this->pathname
            ));
        }

        return $this;
    }


    //

    public function truncate(): self
    {

        // Make sure file pointer is at the top
        $this->rewind();
        $truncate = $this->ftruncate(size: 0);

        if ($truncate) {
            $this->addStats(FilesystemStatsStatusEnum::SUCCESS, FileActionEnum::TRUNCATE);
        } else {
            $this->addStats(FilesystemStatsStatusEnum::FAILURE, FileActionEnum::TRUNCATE);
            throw new FileTruncateError(sprintf(
                "Could not truncate file %s",
                $this->pathname
            ));
        }

        return $this;
    }


    // Deletes the file

    public function delete(): true
    {

        $unlink = unlink($this->pathname);

        if ($unlink) {
            $this->addStats(FilesystemStatsStatusEnum::SUCCESS, FileActionEnum::DELETE);
        } else {
            $this->addStats(FilesystemStatsStatusEnum::FAILURE, FileActionEnum::DELETE);
            throw new FileDeleteError(sprintf(
                "Could not delete file %s",
                $this->pathname
            ));
        }

        return $unlink;
    }


    // An alias of the `delete()` method

    public function remove(): true
    {

        return $this->delete();
    }


    // Converts relocate method to file action

    public static function convertRelocateMethodToFileAction(
        RelocateMethodEnum $relocate_method,
    ): FileActionEnum {

        return match ($relocate_method) {
            RelocateMethodEnum::COPY => FileActionEnum::COPY,
            RelocateMethodEnum::MOVE => FileActionEnum::MOVE,
        };
    }


    //

    public function relocate(
        RelocateMethodEnum $relocate_method,
        FilePath $dest_file_path,
        DuplicateHandlingOptionsEnum $duplicate_handling = DuplicateHandlingOptionsEnum::ABORT,
        string $duplicate_pattern = Filesystem::DEFAULT_BASENAME_DUPLICATE_PATTERN,
    ): self {

        $dest_pathname = $dest_file_path->__toString();
        $file_action = self::convertRelocateMethodToFileAction($relocate_method);

        // Destination path name is backed up by an existing file
        if ($dest_file_path->existsInFilesystem()) {

            $handle_existing = $this->handleExistingFile($file_action, $dest_file_path, $duplicate_handling, $duplicate_pattern);

            if ($handle_existing) {

                $dest_file_path = $handle_existing;
                $dest_pathname = $dest_file_path->__toString();

                // Internal function `copy()` will return `false` when both path names match, so instead just return self
                // Internal `rename()`, when both pathnames are identical, will return `true`
            } elseif ($this->pathname === realpath($dest_pathname)) {

                return $this;
            }
        }

        $internal_function_name = match ($relocate_method) {
            RelocateMethodEnum::COPY => 'copy',
            RelocateMethodEnum::MOVE => 'rename',
        };
        $action_result = $internal_function_name($this->pathname, $dest_pathname);

        if ($action_result) {

            $this->addStats(FilesystemStatsStatusEnum::SUCCESS, $file_action, extra_params: [$dest_pathname]);

            return new File($dest_file_path, $this->filesystem_stats);

        } else {

            $this->addStats(FilesystemStatsStatusEnum::FAILURE, $file_action, extra_params: [$dest_pathname]);

            if ($relocate_method === RelocateMethodEnum::COPY) {
                $error_class_name = FileCopyError::class;
                $action_word = 'copy';
            } else {
                $error_class_name = FileMoveError::class;
                $action_word = 'move';
            }

            throw new $error_class_name(sprintf(
                "Could not %s file %s to %s",
                $action_word,
                $this->pathname,
                $dest_pathname
            ));
        }
    }


    //

    public function copy(
        FilePath $dest_file_path,
        DuplicateHandlingOptionsEnum $duplicate_handling = DuplicateHandlingOptionsEnum::ABORT,
        string $duplicate_pattern = Filesystem::DEFAULT_BASENAME_DUPLICATE_PATTERN,
    ): self {

        return $this->relocate(
            RelocateMethodEnum::COPY,
            $dest_file_path,
            $duplicate_handling,
            $duplicate_pattern
        );
    }


    //

    public function copyTo(
        Directory $dest_directory,
        DuplicateHandlingOptionsEnum $duplicate_handling = DuplicateHandlingOptionsEnum::ABORT,
        string $duplicate_pattern = Filesystem::DEFAULT_BASENAME_DUPLICATE_PATTERN,
    ): self {

        $dest_file_path = clone $dest_directory->file_path;
        $dest_file_path->appendSegment($this->getBasename());

        return $this->copy($dest_file_path, $duplicate_handling, $duplicate_pattern);
    }


    // Duplicates the file

    public function duplicate(string $duplicate_pattern = Filesystem::DEFAULT_BASENAME_DUPLICATE_PATTERN): self
    {

        return $this->copy($this->file_path, DuplicateHandlingOptionsEnum::KEEP_BOTH, $duplicate_pattern);
    }


    //

    public function move(
        FilePath $dest_file_path,
        DuplicateHandlingOptionsEnum $duplicate_handling = DuplicateHandlingOptionsEnum::ABORT,
        string $duplicate_pattern = Filesystem::DEFAULT_BASENAME_DUPLICATE_PATTERN
    ): self {

        return $this->relocate(
            RelocateMethodEnum::MOVE,
            $dest_file_path,
            $duplicate_handling,
            $duplicate_pattern
        );
    }


    //

    public function moveTo(
        Directory $dest_directory,
        DuplicateHandlingOptionsEnum $duplicate_handling = DuplicateHandlingOptionsEnum::ABORT,
        string $duplicate_pattern = Filesystem::DEFAULT_BASENAME_DUPLICATE_PATTERN
    ): self {

        $dest_file_path = clone $dest_directory->file_path;
        $dest_file_path->appendSegment($this->getBasename());

        return $this->move($dest_file_path, $duplicate_handling, $duplicate_pattern);
    }

    /**
     *
     *
     * @return bool|null "null" value when not able to determine
     */
    public function isEmpty(): bool
    {

        $size = $this->getSize();

        if ($size === false) {
            throw new \Error("Could not determine file size");
        }

        return ($size === 0);
    }


    // @param $duplicate_handle When `false`, throw exception, when `true` - return existing file, or use a duplicate base name pattern string

    public static function create(
        FilePath $file_path,
        bool|string $duplicate_handle = false,
        // By reference, because on error it will throw an exception and there will be no way to return filesystem stats
        ?FilesystemStats &$filesystem_stats = null,
    ): self {

        $pathname = $file_path->__toString();

        if ($file_path->existsInFilesystem()) {

            $this->addStats(FilesystemStatsStatusEnum::FOUND, FileActionEnum::CREATE, $pathname);

            if ($duplicate_handle === false) {

                throw new DuplicateFileException(sprintf(
                    "File %s already exists",
                    $file_path->__toString()
                ));

            } elseif ($duplicate_handle === true) {

                return new self($file_path);
            }

            $pattern = ($duplicate_handle === true)
                ? Filesystem::DEFAULT_BASENAME_DUPLICATE_PATTERN
                : $duplicate_handle;
            $file_path = Filesystem::buildUniqueBasename($file_path, $pattern);
        }

        $file = fopen($pathname, mode: 'c+');

        if ($file === false) {
            throw new FileCreateError(sprintf(
                "Could not create file %s",
                $pathname
            ));
        }

        fclose($file);

        return new self($file_path);
    }


    // Renames the file

    public function rename(
        string $new_name,
        bool $with_extension = false,
        DuplicateHandlingOptionsEnum $duplicate_handling = DuplicateHandlingOptionsEnum::ABORT,
        string $duplicate_pattern = Filesystem::DEFAULT_BASENAME_DUPLICATE_PATTERN,
    ): self {

        if (!$with_extension) {
            $extension = $this->getExtension();
            if ($extension) {
                $new_name .= (Path::FILENAME_EXTENSION_PREFIX . $extension);
            }
        }

        return $this->traitRename($new_name, $duplicate_handling, $duplicate_pattern);
    }


    // Changes file extension

    public function changeExtension(
        string $new_extension,
        DuplicateHandlingOptionsEnum $duplicate_handling = DuplicateHandlingOptionsEnum::ABORT,
        string $duplicate_pattern = Filesystem::DEFAULT_BASENAME_DUPLICATE_PATTERN,
    ): self {

        $file_path = clone $this->file_path;
        $file_path->setExtension($new_extension);
        $basename = $file_path->getBasename();

        return $this->rename(
            $basename,
            with_extension: true,
            duplicate_handling: $duplicate_handling,
            duplicate_pattern: $duplicate_pattern,
        );
    }


    // Compresses the file into a zip archive file

    public function zip(): FileZip
    {


    }
}
