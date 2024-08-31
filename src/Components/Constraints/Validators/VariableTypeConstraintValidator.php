<?php

declare(strict_types=1);

namespace LWP\Components\Constraints\Validators;

use LWP\Components\Violations\VariableTypeViolation;
use LWP\Components\Constraints\VariableTypeConstraint;

class VariableTypeConstraintValidator extends ConstraintValidator implements ConstraintValidatorInterface
{
    public function __construct(
        VariableTypeConstraint $constraint,
    ) {

        parent::__construct($constraint);
    }


    //

    public function validate(mixed $value): true|VariableTypeViolation
    {

        // Utilize "strtolower" to perform case-insensitive search.
        $set = array_map('strtolower', $this->constraint->getValue());
        $value_type = strtolower(gettype($value));

        if (!in_array($value_type, $set)) {
            return new VariableTypeViolation($set, $value_type, $value_type);
        }

        return true;
    }
}
