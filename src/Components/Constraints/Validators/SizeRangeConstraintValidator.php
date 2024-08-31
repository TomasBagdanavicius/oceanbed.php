<?php

declare(strict_types=1);

namespace LWP\Components\Constraints\Validators;

use LWP\Components\Constraints\SizeRangeConstraint;
use LWP\Components\Constraints\Violations\SizeRangeConstraintViolation;

class SizeRangeConstraintValidator extends ConstraintValidator implements ConstraintValidatorInterface
{
    public function __construct(
        SizeRangeConstraint $constraint,
    ) {

        parent::__construct($constraint);
    }


    // Checks if the given value does not violate the "SizeRange" constraint requirements.

    public function validate(int|float|NumberDataTypeValue|Sizeable $value): true|SizeRangeConstraintViolation
    {

        if ($value instanceof NumberDataTypeValue) {
            $value = $value->getValue();
        } elseif ($value instanceof Sizeable) {
            $value = $value->getSize();
        }

        return ($value < $this->constraint->getMinSize() || $value > $this->constraint->getMaxSize())
            ? new SizeRangeConstraintViolation($this, $value)
            : true;
    }
}
