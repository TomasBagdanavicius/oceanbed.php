<?php

declare(strict_types=1);

namespace LWP\Database;

use LWP\Common\Array\ArrayCollection;
use LWP\Common\Iterators\ColumnCreateIterator;
use LWP\Database\SyntaxBuilders\BasicSqlQueryBuilder;
use LWP\Components\Datasets\Interfaces\DatasetResultInterface;
use LWP\Filesystem\Path\FilePath;
use LWP\Database\Exceptions\EmptyResultException;
use LWP\Common\Iterators\CsvFileCreateIterator;
use LWP\Database\Exceptions\ColumnNotFoundException;

class Result implements \IteratorAggregate, \Countable, DatasetResultInterface
{
    public const GET_ARRAY = 1;
    public const GET_OBJECT = 2;


    // Weak reference of "BasicSqlQueryBuilder".
    public readonly \WeakReference $basic_sql_query_builder;


    public function __construct(
        public readonly \mysqli_result $mysqli_result,
        public readonly ?string $statement = null,
        public readonly array $statement_params = [],
        public readonly ?string $query = null
    ) {

    }


    // Frees the memory associated with the result.

    public function __destruct()
    {

        $this->mysqli_result->close();
    }


    // Gets MySQLi result object.

    public function getMysqliResult(): \mysqli_result
    {

        return $this->mysqli_result;
    }


    // A shorthand alternative to get the number of rows in the result set.

    public function count(): int
    {

        return $this->mysqli_result->num_rows;
    }


    // Get fetch method params by format flag.

    public static function getFetchMethodParamsByFormat(int $format): array
    {

        $result = [];

        $result[] = ($format === self::GET_OBJECT)
            ? 'fetch_object'
            : 'fetch_array';

        $result[] = ($format === self::GET_OBJECT)
            ? 'stdClass'
            : MYSQLI_ASSOC;

        return $result;
    }


    // Gets the result iterator.

    public function getIterator(): \Traversable
    {

        /* Keeping custom iterator, because with the built-in iterator getting "Error: mysqli_result object is already closed". This also leaves more options for functionality expansion. */
        #return $this->mysqli_result->getIterator();
        return new Iterators\ResultIterator($this);
    }


    // Iterates through each row in the result set.

    public function each(callable $callback, int $format = self::GET_OBJECT): ?self
    {

        if (!$this->count()) {
            return null;
        }

        [$func_name, $param] = self::getFetchMethodParamsByFormat($format);

        while ($row = $this->mysqli_result->{$func_name}($param)) {

            $callback_result = $callback($row);

            if ($callback_result === null) {
                continue;
            } elseif ($callback_result === false) {
                break;
            }
        }

        return $this;
    }


    // Gets just the first row.

    public function getOne(int $format = self::GET_OBJECT): null|object|array
    {

        if (!$this->count()) {
            return null;
        }

        return ($format === self::GET_OBJECT)
            ? $this->mysqli_result->fetch_object()
            : $this->mysqli_result->fetch_array(MYSQLI_ASSOC);
    }


    //

    public function getFirst(): ?array
    {

        return $this->getOne(self::GET_ARRAY);
    }


    // Retrieves all rows.

    public function toArray(bool $first_elem_only = false, ?string $group_by = null): array
    {

        $list = [];
        $add_to_list = function (object|array $row, mixed $value = null) use (&$list, $group_by): void {

            $value = ($value ?? $row);

            if ($group_by) {

                if (isset($row->{$group_by})) {
                    $list[$row->{$group_by}][] = $value;
                } else {
                    throw new ColumnNotFoundException(sprintf(
                        "Column %s was not found",
                        $group_by
                    ));
                }

            } else {

                $list[] = $value;
            }
        };

        $this->each(function (object|array $row) use ($add_to_list, $first_elem_only): void {

            if (!$first_elem_only) {
                $add_to_list($row);
            } else {
                // Since "reset(object)" is depreciated.
                foreach ($row as $val) {
                    break;
                }
                $add_to_list($row, $val);
            }

        });

        return $list;
    }


    // Retrieves all rows and creates a collection object.

    public function getCollection(bool $first_elem_only = false): ArrayCollection
    {

        $collection = new ArrayCollection();

        $this->each(function (object|array $row) use ($collection, $first_elem_only): void {

            if (!$first_elem_only) {
                $collection->add($row);
            } else {
                // Since "reset(object)" is depreciated.
                foreach ($row as $val) {
                    break;
                }
                $collection->add($val);
            }

        });

        return $collection;
    }


    // Puts data to a CSV file.

    public function putDataToCsvFile(FilePath $file_path, array $column_list = null, bool $add_quotes = true): bool
    {

        if (!$this->count()) {
            throw new EmptyResultException("The result is empty and there is nothing to export into \"$file_path\".");
        }

        if (!$column_list) {

            $field_data = $this->getMysqliResult()->fetch_fields();
            $default_column_data = $column_list = [];

            foreach ($field_data as $data) {

                $default_column_data[$data->name] = '';
                $column_list[$data->name] = $data->name;
            }

        } else {

            $default_column_data = array_fill_keys(array_keys($column_list), '');
        }

        $iterator = new ColumnCreateIterator($this->getIterator(), $default_column_data, $column_list);

        // MySQL's int, decimal reveal themselves as strings in PHP.
        // If you enter a scientific number into MySQL, it will convert it to full number.
        $iterator = new CsvFileCreateIterator($iterator, $file_path, [
            'NULL' => null,
        ], '"');

        foreach ($iterator as $val) {
        };

        return true;
    }


    //

    public function setBasicSqlBuilder(BasicSqlQueryBuilder $basic_sql_query_builder): void
    {

        $this->basic_sql_query_builder = \WeakReference::create($basic_sql_query_builder);
    }
}
