<?php

declare(strict_types=1);

namespace LWP\Common;

use LWP\Common\String\Clause\SortByComponent;
use LWP\Common\Enums\NamedOperatorsEnum;
use LWP\Common\Conditions\Condition;
use LWP\Common\Conditions\ConditionGroup;

class Criteria
{
    public readonly ConditionGroup $base_condition_group;
    protected null|SortByComponent|\Closure $sort_by = null;
    protected int $limit = -1; // Unlimited.
    protected int $offset = 0;


    public function __construct()
    {

        $this->base_condition_group = new ConditionGroup();
    }


    // Builds an SQL-style string describing the criteria.

    public function __toString(): string
    {

        $result = '';

        if ($this->base_condition_group->count()) {
            $result .= ('WHERE ' . $this->base_condition_group->__toString());
        }

        if ($this->sort_by) {

            if ($result) {
                $result .= ' ';
            }

            // Either string or SortByComponent.
            $result .= ('ORDER BY ' . (string)$this->sort_by);
        }

        if ($this->offset) {

            if ($result) {
                $result .= ' ';
            }

            $result .= ('OFFSET ' . $this->offset);
        }

        if ($this->limit >= 0) {

            if ($result) {
                $result .= ' ';
            }

            $result .= ('LIMIT ' . $this->limit);
        }

        return $result;
    }


    // Appends a condition or condition group to the clause statement.

    public function condition(
        Condition|ConditionGroup $condition_object,
        NamedOperatorsEnum $named_operator = NamedOperatorsEnum::AND
    ): self {

        $this->base_condition_group->add($condition_object, $named_operator);

        return $this;
    }


    // Adds sort component.

    public function sort(SortByComponent|string|\Closure $sort_by): self
    {

        if (is_string($sort_by)) {
            $sort_by = SortByComponent::fromString($sort_by);
        }

        $this->sort_by = $sort_by;

        return $this;
    }


    // Gets sort component.

    public function getSort(): null|SortByComponent|\Closure
    {

        return $this->sort_by;
    }


    // Sets the limit number.

    public function limit(int $limit): self
    {

        if ($limit < -1) {
            throw new \ValueError(sprintf(
                "Criteria's limit cannot be smaller than -1, got %d",
                $limit
            ));
        }

        $this->limit = $limit;

        return $this;
    }


    // Gets the limit number.

    public function getLimit(): int
    {

        return $this->limit;
    }


    // Sets the offset number.

    public function offset(int $offset): self
    {

        if ($offset < 0) {
            throw new \ValueError(sprintf(
                "Criteria's offset cannot be smaller than 0, got %d",
                $offset
            ));
        }

        $this->offset = $offset;

        return $this;
    }


    // Gets the offset number.

    public function getOffset(): int
    {

        return $this->offset;
    }
}
