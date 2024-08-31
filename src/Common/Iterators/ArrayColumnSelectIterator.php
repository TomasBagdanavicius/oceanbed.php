<?php

declare(strict_types=1);

namespace LWP\Common\Iterators;

use LWP\Common\String\Format;
use LWP\Common\Array\Exceptions\ColumnKeysMismatchException;

class ArrayColumnSelectIterator extends \IteratorIterator
{
    public const THROW_WHEN_ELEMENT_NOT_FOUND = 1;


    public readonly array $column_list_flipped;
    public readonly int $column_list_count;


    public function __construct(
        \Traversable $iterator,
        protected array $column_list,
        ?string $class = null,
        protected ?int $options = null
    ) {

        $this->column_list_flipped = array_flip($this->column_list);
        $this->column_list_count = count($this->column_list);

        parent::__construct($iterator, $class);
    }


    // Intercepts the current element and selects the desired members of the array

    public function current(): mixed
    {

        $current_element = parent::current();

        if (is_array($current_element) && $current_element) {

            $current_element = array_intersect_key($current_element, $this->column_list_flipped);

            if ($this->options & self::THROW_WHEN_ELEMENT_NOT_FOUND && $this->column_list_count !== count($current_element)) {

                $current_element_count = count($current_element);

                throw new ColumnKeysMismatchException(sprintf(
                    "Found %d %s in item at index \"%s\", %d %s expected",
                    $current_element_count,
                    Format::getCasualSingularOrPlural($current_element_count, 'element'),
                    parent::key(),
                    $this->column_list_count,
                    Format::getCasualSingularOrPlural($this->column_list_count, 'element')
                ));
            }
        }

        return $current_element;
    }
}
