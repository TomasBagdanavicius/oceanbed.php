<?php

declare(strict_types=1);

namespace LWP\Common\Iterators;

class GroupByOneColumnIterator extends \FilterIterator implements ColumnDataIteratorInterface, AccumulativeIteratorInterface
{
    /* Ignore empty string values. */
    public const SUPPRESS_EMPTY = 1;
    /* Create array elements for all branches, including the ones that have just one value. */
    public const BRANCH_ALL = 2;

    private array $storage_index = [];
    private array $storage = [];


    public function __construct(
        \Iterator $iterator,
        private string $primary_column_name,
        private string $primary_column_title,
        private ?int $flags = self::SUPPRESS_EMPTY
    ) {

        parent::__construct($iterator);
    }


    // Gets column list.

    public function getColumnList(): array
    {

        return [
            $this->primary_column_name => $this->primary_column_title,
        ];
    }


    // Gets default column data.

    public function getDefaultColumnData(): array
    {

        return [
            $this->primary_column_name => '',
        ];
    }


    // Checks whether the group member already exist. If yes, adds additional data to that group.

    public function accept(): bool
    {

        $current = parent::current();

        $key = array_search(strtolower($current[$this->primary_column_name]), $this->storage_index);

        if ($key !== false) {

            $suppress_empty = ($this->flags & self::SUPPRESS_EMPTY);
            $branch_all = ($this->flags & self::BRANCH_ALL);

            foreach ($current as $k => $v) {

                if ((!$suppress_empty || ($suppress_empty && $v != '')) && $k != $this->primary_column_name) {

                    if (!isset($this->storage[$key][$k])) {

                        $this->storage[$key][$k] = $branch_all
                            ? [$v]
                            : $v;

                    } else {

                        $store = $this->storage[$key][$k];

                        if (is_string($store) && $store != $v) {
                            $this->storage[$key][$k] = [$store, $v];
                        } elseif (is_array($store) && !in_array($v, $store)) {
                            $this->storage[$key][$k] = array_merge($store, [$v]);
                        }
                    }
                }
            }

            return false;
        }

        return true;
    }


    // Returns the current unique group column value.

    public function current(): mixed
    {

        $current = parent::current();

        $key = array_search(strtolower($current[$this->primary_column_name]), $this->storage_index);

        if ($key === false) {

            $modified_piece = [
                $this->primary_column_name => $current[$this->primary_column_name],
            ];

            $suppress_empty = ($this->flags & self::SUPPRESS_EMPTY);

            foreach ($current as $k => $v) {

                if ($k != $this->primary_column_name && (!$suppress_empty || ($suppress_empty && $v != ''))) {

                    $modified_piece[$k] = ($this->flags & self::BRANCH_ALL)
                        ? [$v]
                        : $v;
                }
            }

            $this->storage[] = $modified_piece;
            $this->storage_index[] = strtolower($current[$this->primary_column_name]);
        }

        return $current[$this->primary_column_name];
    }


    // Retrieves the storage.

    public function getStorage(): array
    {

        return $this->storage;
    }


    // Gets storage iterator.

    public function getStorageIterator(): \Traversable
    {

        return new \ArrayIterator($this->getStorage());
    }
}
