<?php

declare(strict_types=1);

namespace LWP\Common\Conditions;

/* Instead of extending "RecursiveIteratorIterator" it implements the "RecursiveIterator" interface. The rationale was to control the "position" property in order to be able to get current or next position, or check if iterator has ended. Most of these features were used while building and testing the "ConditionGroup" class. */
class ConditionGroupIterator implements \RecursiveIterator
{
    protected int $position = 0;


    public function __construct(
        public readonly array $data
    ) {

    }


    // Gets current position.

    public function getPosition(): int
    {

        return $this->position;
    }


    // Gets next position.

    public function getNextPosition(): int
    {

        $next_position = ($this->getPosition() + 1);

        return ($next_position <= $this->getDataLength())
            ? $next_position
            : null;
    }


    // Gets the number of elements in the data set.

    public function getDataLength(): int
    {

        return count($this->data);
    }


    // Tells if it has walked through all the position.

    public function hasEnded(): bool
    {

        return ($this->getNextPosition() === $this->getDataLength());
    }


    // Sets the position as if the iterator has finished.

    public function finish(): void
    {

        $this->position = $this->getDataLength();
    }


    // Gets the number of child elements in this branch, unless it is not a branch.

    public function getChildrenLength(): ?int
    {

        if (!$this->hasChildren()) {
            return null;
        }

        return count($this->current()[0]->getData());
    }


    // Gets child data recursive iterator.

    public function getChildren(): ?\RecursiveIterator
    {

        if (!$this->hasChildren()) {
            return null;
        }

        return new self($this->current()[0]->getData());
    }


    // Tells if current element is a branch with children.

    public function hasChildren(): bool
    {

        return ($this->current()[0] instanceof ConditionGroup);
    }


    // Gets next data element, provided that it hasn't reached the end of iteration.

    public function getNextData(): ?\SplFixedArray
    {

        $next_position = $this->getNextPosition();

        return isset($this->data[$next_position])
            ? $this->data[$next_position]
            : null;
    }


    // Return the current element.

    public function current(): mixed
    {

        return $this->data[$this->position];
    }


    // Return the key of the current element.

    public function key(): int
    {

        return $this->position;
    }


    // Move forward to the next element.

    public function next(): void
    {

        $this->position++;
    }


    // Rewind the iterator to the first element of the top level inner iterator.

    public function rewind(): void
    {

        $this->position = 0;
    }


    // Check whether the current position is valid.

    public function valid(): bool
    {

        return isset($this->data[$this->position]);
    }
}
