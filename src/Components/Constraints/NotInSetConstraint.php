<?php

declare(strict_types=1);

namespace LWP\Components\Constraints;

class NotInSetConstraint extends Constraint
{
    public function __construct(
        array $not_in_set,
    ) {

        parent::__construct($not_in_set);
    }


    // Gets the given constraint set.

    public function getSet(): array
    {

        return $this->value;
    }


    // Gets metadata.

    public function getDefinition(): array
    {

        return [
            'not_in_set' => $this->value,
        ];
    }
}
