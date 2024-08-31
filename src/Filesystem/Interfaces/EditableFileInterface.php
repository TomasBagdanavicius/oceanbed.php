<?php

declare(strict_types=1);

namespace LWP\Filesystem\Interfaces;

use LWP\Filesystem\Filesystem;
use LWP\Filesystem\Enums\FileTypeEnum;
use LWP\Filesystem\FileType\FileFormats\FileZip;

interface EditableFileInterface
{
    // Gets information about the file

    public function getInfo(): array;


    // Gets enumerated value of the file type

    public function getTypeEnumerated(): FileTypeEnum;


    // Gets the filename without extension part
    // Compatible with `SplFileInfo::getFilename()`

    public function getFilename(): string;


    // Gets file size
    // Compatible with `SplFileInfo::getSize()`

    public function getSize(): int|false;


    // Gets real file size (including inner items)
    // Compatible with `SplFileInfo::getSize()`

    public function getRealSize(): int|false;


    // Duplicates the file

    public function duplicate(string $suffix): self;


    // Truncates/Empties the file (including inner items)

    public function truncate(): self;


    // Deletes the file

    public function delete(): true;


    // Removes the file

    public function remove(): true;


    // Tells if file is empty

    public function isEmpty(): bool;


    // Compresses the file into a zip archive file

    public function zip(): FileZip;

}
