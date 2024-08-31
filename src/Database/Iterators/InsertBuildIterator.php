<?php

declare(strict_types=1);

namespace LWP\Database\Iterators;

use LWP\Common\Iterators\AccumulativeIteratorInterface;
use LWP\Common\Iterators\ColumnDataIteratorInterface;
use LWP\Database\Table;

class InsertBuildIterator extends \IteratorIterator implements AccumulativeIteratorInterface, \Countable
{
    public const USE_STATEMENT_SYNTAX = 1;

    private array $params = [];
    private int $count = 0;


    public function __construct(
        ColumnDataIteratorInterface $iterator,
        private Table $table,
        private ?int $flags = null,
        ?string $class = null
    ) {

        parent::__construct($iterator, $class);
    }


    // Gets count number.

    public function count(): int
    {

        return $this->count;
    }


    // Intercepts the current element and stores its data.

    public function current(): mixed
    {

        $current = parent::current();

        if ($this->flags & self::USE_STATEMENT_SYNTAX) {

            array_push($this->params, ...array_values($current));

        } else {

            $str = [];

            array_walk($current, function (mixed &$var) use (&$str): void {
                $str[] = $this->table->server->formatVariable($var);
            });

            $this->params[] = '(' . implode(',', $str) . ')';
        }

        $this->count++;

        return $current;
    }


    // Gets the query string.

    public function getQueryString()
    {

        $column_list = $this->getInnerIterator()->getColumnList();

        $trailing_part = (($this->flags & self::USE_STATEMENT_SYNTAX))
            ? rtrim(str_repeat("(" . implode(',', array_fill(0, count($column_list), '?')) . "),", $this->count), ',')
            : implode(',', $this->params);

        $query_statement = "INSERT INTO `" . $this->table->table_name . "`"
            . " " . $this->table->formatFieldListWithBrackets(array_keys($column_list))
            . " VALUES " . $trailing_part . ';';

        return $query_statement;
    }


    // Gets the storage container.

    public function getStorage(): array
    {

        return $this->params;
    }


    // Gets storage iterator.

    public function getStorageIterator(): \Traversable
    {

        return new \ArrayIterator($this->getStorage());
    }
}
