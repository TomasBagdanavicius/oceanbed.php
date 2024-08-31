<?php

declare(strict_types=1);

namespace LWP\Components\Constraints\Validators;

use LWP\Common\Interfaces\Sizeable;
use LWP\Components\Constraints\MaxSizeConstraint;
use LWP\Components\Constraints\Violations\MaxSizeConstraintViolation;
use LWP\Components\DataTypes\Custom\Number\NumberDataTypeValueContainer;

class MaxSizeConstraintValidator extends ConstraintValidator implements ConstraintValidatorInterface
{
    public function __construct(
        MaxSizeConstraint $constraint
    ) {

        parent::__construct($constraint);
    }


    // Checks if the given value does not violate the max size constraint requirements.

    public function validate(int|float|NumberDataTypeValueContainer|Sizeable $value): true|MaxSizeConstraintViolation
    {

        if ($value instanceof NumberDataTypeValueContainer) {
            $value = $value->getValue();
        } elseif ($value instanceof Sizeable) {
            $value = $value->getSize();
        }

        return ($this->constraint->getValue() < $value)
            ? new MaxSizeConstraintViolation($this, $value)
            : true;
    }
}
