<?php

declare(strict_types=1);

namespace LWP\Common\Conditions;

class ConditionMatchFilterIterator extends \FilterIterator
{
    public function __construct(
        \Traversable $iterator,
        protected Condition|ConditionGroup $condition_object,
    ) {

        parent::__construct(
            (($iterator instanceof \IteratorAggregate)
                ? $iterator->getIterator()
                : $iterator)
        );
    }


    //

    public function accept(): bool
    {

        $element = parent::current();

        if ($element instanceof ConditionMatchableInterface) {

            // Condition
            if ($this->condition_object instanceof Condition) {
                return $element->matchCondition($this->condition_object);
                // Condition group
            } else {
                return $element->matchConditionGroup($this->condition_object);
            }
        }

        return parent::accept();
    }
}
