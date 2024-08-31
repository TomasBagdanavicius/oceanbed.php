<?php

declare(strict_types=1);

namespace LWP\Common\Iterators;

class ColumnCreateIterator extends \IteratorIterator implements ColumnDataIteratorInterface
{
    private ?array $primary_keys = null;


    public function __construct(
        \Traversable $iterator,
        /* When column list is not provided, it will check if keys in every iteration matches the first set's keys (primary keys). */
        private ?array $column_list = null,
        /* When not an array, all missing fields will get the given value, which can also be a special null value. */
        private null|array|string $default_column_data = '',
        private ?string $divider = null,
        ?string $class = null,
    ) {

        parent::__construct($iterator, $class);
    }


    // Gets column list.

    public function getColumnList(): array
    {

        if ($this->column_list) {
            return $this->column_list;
        } elseif ($this->primary_keys) {
            return array_combine($this->primary_keys, $this->primary_keys);
        } else {
            throw new \Exception("Column list is not available.");
        }
    }


    // Gets default column data.

    public function getDefaultColumnData(): array
    {

        // When an array of default column data was provided, it must be definitive.
        if (is_array($this->default_column_data)) {

            return $this->default_column_data;

            // Create a new array of all columns where the filled value is the default string or null value.
        } else {

            $keys = array_keys($this->getColumnList());

            return array_combine($keys, array_fill_keys($keys, $this->default_column_data));
        }
    }


    // Intercepts the current element and injects default elements to the data array.

    public function current(): mixed
    {

        $current = parent::current();

        if (!is_array($current)) {
            throw new \TypeError(sprintf("Element at index %s must be of type array; %s given.", (string)$this->key(), gettype($current)));
        }

        if (!$this->column_list) {

            $fields = array_keys($current);

            // When no column list is provided, primary keys must be gathered based on the first row key data.
            if (!$this->primary_keys) {
                $this->primary_keys = $fields;
            } elseif (($diff = array_diff($fields, $this->primary_keys)) || ($diff2 = array_diff($this->primary_keys, $fields))) {
                throw new \Exception(sprintf("Element keys at index %s must match the primary keys at index 0.", (string)$this->key()));
            }

            return $current;

        } else {

            $result = [];

            foreach ($this->column_list as $field_name => $value) {

                if (array_key_exists($field_name, $current)) {
                    $element = $current[$field_name];
                } elseif (is_string($this->default_column_data) || is_null($this->default_column_data)) {
                    $element = $this->default_column_data;
                } elseif (array_key_exists($field_name, $this->default_column_data)) {
                    $element = $this->default_column_data[$field_name];
                } else {
                    throw new \Exception(sprintf(
                        "No default data was found for field \"%s\" at index %d.",
                        $field_name,
                        (string)$this->key()
                    ));
                }

                if ($this->divider && is_array($element)) {
                    $element = implode($this->divider, $element);
                }

                $result[$field_name] = $element;
            }

            return $result;
        }
    }
}
