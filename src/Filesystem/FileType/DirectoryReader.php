<?php

declare(strict_types=1);

namespace LWP\Filesystem\FileType;

use LWP\Filesystem\Iterators\MyFilesystemIterator;
use LWP\Filesystem\Iterators\MyRecursiveDirectoryIterator;
use LWP\Common\Conditions\Condition;
use LWP\Common\Conditions\ConditionGroup;
use LWP\Common\Conditions\ConditionMatchFilterIterator;
use LWP\Common\Iterators\AggregateSizeLimitIterator;
use LWP\Common\String\Clause\SortByComponent;
use LWP\Filesystem\Interfaces\EditableFileInterface;
use LWP\Filesystem\Exceptions\FileNotFoundException;

class DirectoryReader implements \IteratorAggregate, \Countable
{
    public const RECURSE = 1;
    public const KEY_AS_RELATIVE_PATHNAME = 2;
    public const SELF_FIRST = 4;
    public const CHILD_FIRST = 8;


    protected \Traversable $iterator;


    public function __construct(
        public readonly Directory $directory,
        int $flags = 0
    ) {

        // No recursion
        if (!($flags & self::RECURSE)) {

            $params = [
                $this->directory
            ];

            if ($flags & self::KEY_AS_RELATIVE_PATHNAME) {
                $params[] = MyFilesystemIterator::getDefaultFlags() | MyFilesystemIterator::KEY_AS_RELATIVE_PATHNAME;
            }

            $this->iterator = new MyFilesystemIterator(...$params);

            // With recursion
        } else {

            // LEAVES_ONLY is not in the mix, because it can be replaced by "type = file" condition
            $param_flags = ($flags & self::SELF_FIRST || !($flags & self::CHILD_FIRST))
                ? \RecursiveIteratorIterator::SELF_FIRST
                : \RecursiveIteratorIterator::CHILD_FIRST;

            $this->iterator = new \RecursiveIteratorIterator(
                new MyRecursiveDirectoryIterator($this->directory),
                $param_flags
            );
        }
    }


    //

    public function getIterator(): \Traversable
    {

        return $this->iterator;
    }


    //

    public function sort(string $sort_string): self
    {

        $array = iterator_to_array($this->iterator);
        $first_element = $array[array_key_first($array)];
        $sort_handler = SortByComponent::getSortHandlerForIndexableObject($first_element, $sort_string);
        $this->iterator = new \ArrayIterator($array);
        $this->iterator->uasort($sort_handler);

        return $this;
    }


    //

    public function limit(int $primary_number, ?int $secondary_number = null): self
    {

        $limit_number = $secondary_number ?? $primary_number;
        $offset_number = ($secondary_number !== null)
            ? $primary_number
            : 0;

        // Zero value is causing a "seek" out of bounds error.
        if ($limit_number === 0) {
            throw new \ValueError("Limit number cannot be equal to zero");
        }

        if ($limit_number < -1) {
            throw new \ValueError("Limit number cannot be smaller than -1");
        }

        if ($offset_number < 0) {
            throw new \ValueError("Offset number cannot be smaller than 0");
        }

        $this->iterator = new \LimitIterator(
            $this->iterator,
            $offset_number,
            $limit_number
        );

        return $this;
    }


    //

    public function offset(int $offset_number): self
    {

        if ($offset_number < 0) {
            throw new \ValueError("Offset number cannot be smaller than 0");
        }

        $this->iterator = new \LimitIterator($this->iterator, $offset_number);

        return $this;
    }


    //

    public function conditions(Condition|ConditionGroup $condition_object): self
    {

        $this->iterator = new ConditionMatchFilterIterator($this->iterator, $condition_object);

        return $this;
    }


    //

    public function limitSize(int $size_limit_number): self
    {

        $this->iterator = new AggregateSizeLimitIterator($this->iterator, $size_limit_number);

        return $this;
    }


    //

    public function forEach(\Closure $callback): void
    {

        foreach ($this as $file) {
            $callback($file);
        }
    }


    //

    public function getFirst(): ?\SplFileInfo
    {

        foreach ($this as $file) {
            return $file;
        }

        return null;
    }


    //

    public function count(): int
    {

        return iterator_count($this->getIterator());
    }


    //
    /* Basename is the main unique property */

    public function find(string $basename, bool $throw = false): ?EditableFileInterface
    {

        foreach ($this as $file) {
            if ($file->getBasename() === $basename) {
                return $file;
            }
        }

        if ($throw) {
            $separator = $this->directory->file_path->getDefaultSeparator();
            $pathname = ($this->directory->pathname . $separator . $basename);
            throw new FileNotFoundException(sprintf(
                "File %s was not found",
                $pathname
            ));
        }

        return null;
    }
}
