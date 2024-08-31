<?php

declare(strict_types=1);

namespace LWP\Components\Datasets\Relationships;

use LWP\Common\Exceptions\EmptyElementException;

class RelationshipNodeKey implements \Stringable, \IteratorAggregate
{
    public const SEPARATOR = '.';

    public readonly array $parts;
    public readonly int $length;
    public readonly bool $is_single;


    public function __construct(
        public readonly string|int $node_key,
        public readonly int $max_size = 5
    ) {

        #todo: assert
        if (!$node_key) {
            throw new EmptyElementException("Relationship node key cannot be empty.");
        }

        // This is a single digit part input.
        $this->is_single = (is_integer($node_key) || ctype_digit($node_key));

        $parts = self::parse($node_key, $max_size);
        $parts_count = count($parts);

        if ($parts_count > $max_size) {
            throw new \OutOfRangeException(
                "Relationship node key must not consist of more than $max_size parts, $parts_count given."
            );
        }

        $this->parts = $parts;
        $this->length = $parts_count;
    }


    //

    public function __toString(): string
    {

        return implode(self::SEPARATOR, $this->parts);
    }


    //

    public function getIterator(): \Traversable
    {

        return new \ArrayIterator($this->parts);
    }


    //

    public function get(int $position, bool $accept_single = false, bool $throw_on_zero = false): ?int
    {

        #todo: range assert
        if ($position < 1 || $position > $this->max_size) {
            throw new \OutOfRangeException("Position can range from 1 to 5, given $position.");
        }

        if ($accept_single && $this->is_single) {
            return intval($this->node_key);
        }

        $key_part = $this->parts[$position - 1];

        if ($throw_on_zero && !$key_part) {
            throw new \OutOfBoundsException("No element was found at position $position.");
        }

        return intval($key_part);
    }


    //

    public static function parse(string|int $node_key, ?int $max_size = null): array
    {

        $parts = explode(self::SEPARATOR, (string)$node_key);

        if ($max_size !== null && ($count = count($parts)) < $max_size) {
            $parts += array_fill($count, ($max_size - $count), 0);
        }

        return $parts;
    }


    //

    public static function fromArray(array $array, array $params = []): self
    {

        return new self(implode(self::SEPARATOR, $array), ...$params);
    }
}
