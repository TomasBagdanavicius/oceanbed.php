<?php

declare(strict_types=1);

namespace LWP\Components\Constraints;

use LWP\Common\Interfaces\Sizeable;
use LWP\Components\Definitions\MaxDefinition;

class MaxSizeConstraint extends Constraint
{
    public function __construct(
        int|float|Sizeable $value
    ) {

        if ($value instanceof Sizeable) {
            $value = $value->getSize();
        }

        parent::__construct($value);
    }


    // Gets the compact definition array.

    public function getDefinition(): array
    {

        return [
            MaxDefinition::DEFINITION_NAME => $this->getValue(),
        ];
    }


    // Checks whether there are no collision issues with a given associated constraint (normally participating in the same group of constraints).

    public function collisionAssistance(
        Constraint $associated_constraint
    ): true {

        switch (get_class($associated_constraint)) {

            case (__NAMESPACE__ . '\MinSizeConstraint'):

                $min_size = $associated_constraint->getValue();

                if ($min_size > $this->value) {
                    throw new \RangeException(sprintf(
                        "Minimum size constraint value (%s) cannot be higher than the maximum size constraint value (%s)",
                        $min_size,
                        $this->value
                    ));
                }

                break;

            case (__NAMESPACE__ . '\SizeRangeConstraint'):

                $related_max = $associated_constraint->getMaxSize();

                if ($related_max != $this->value) {
                    throw new \RangeException(sprintf(
                        "Maximum value in the range constraint (%s) must be equal to the one in the maximum size constraint (%s)",
                        $related_max,
                        $this->value
                    ));
                }

                break;
        }

        return true;
    }
}
