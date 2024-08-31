<?php

declare(strict_types=1);

namespace LWP\Components\Constraints\Validators;

use LWP\Components\Constraints\MinSizeConstraint;
use LWP\Components\Constraints\Violations\MinSizeConstraintViolation;
use LWP\Common\Interfaces\Sizeable;

class MinSizeConstraintValidator extends ConstraintValidator implements ConstraintValidatorInterface
{
    public function __construct(
        MinSizeConstraint $constraint,
    ) {

        parent::__construct($constraint);
    }


    // Checks if the given value does not violate the min size constraint requirements.

    public function validate(int|float|Sizeable $value): true|MinSizeConstraintViolation
    {

        if ($value instanceof Sizeable) {
            $value = $value->getSize();
        }

        if ($this->constraint->getValue() > $value) {

            return new MinSizeConstraintViolation($this, $value);
        }

        return true;
    }
}
