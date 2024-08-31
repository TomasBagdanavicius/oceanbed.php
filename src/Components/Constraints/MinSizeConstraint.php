<?php

declare(strict_types=1);

namespace LWP\Components\Constraints;

use LWP\Common\Interfaces\Sizeable;
use LWP\Components\Definitions\MinDefinition;

class MinSizeConstraint extends Constraint
{
    public function __construct(
        int|float|Sizeable $value,
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
            MinDefinition::DEFINITION_NAME => $this->getValue(),
        ];
    }


    // Checks whether there are no collision issues with a given associated constraint (normally participating in the same group of constraints).

    public function collisionAssistance(Constraint $associated_constraint): true
    {

        switch (get_class($associated_constraint)) {

            case (__NAMESPACE__ . '\MaxSizeConstraint'):

                if (($max_size = $associated_constraint->getValue()) < $this->value) {
                    throw new \Exception(
                        "Maximum size constraint value ($max_size) cannot be lower than the minimum size constraint value ({$this->value})."
                    );
                }

                break;

            case (__NAMESPACE__ . '\SizeRangeConstraint'):

                if (($related_min = $associated_constraint->getMinSize()) != $this->value) {
                    throw new \Exception(
                        "Minimum value in the range constraint ($related_min) must be equal to the one in the minimum size constraint ({$this->value})."
                    );
                }

                break;
        }

        return true;
    }
}
