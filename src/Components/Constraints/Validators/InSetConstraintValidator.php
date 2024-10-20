<?php

declare(strict_types=1);

namespace LWP\Components\Constraints\Validators;

use LWP\Components\Constraints\InSetConstraint;
use LWP\Components\Constraints\Violations\InSetConstraintViolation;
use LWP\Components\DataTypes\Natural\String\StringDataTypeValueContainer;
use LWP\Components\DataTypes\Natural\Integer\IntegerDataTypeValueContainer;

class InSetConstraintValidator extends ConstraintValidator implements ConstraintValidatorInterface
{
    public function __construct(
        InSetConstraint $constraint,
    ) {

        parent::__construct($constraint);
    }


    //

    public function validate(
        string|StringDataTypeValueContainer|array|IntegerDataTypeValueContainer|int $value
    ): true|InSetConstraintViolation {

        $set = $this->constraint->getSet();

        if (gettype($value) === 'object') {
            $value = $value->getValue();
        }

        if (
            ((is_string($value) || is_int($value)) && !in_array($value, $set))
            || (is_array($value) && ($missing_values = array_diff($value, $set)))
        ) {

            return new InSetConstraintViolation(
                $this,
                $value,
                // Missing value(s).
                ($missing_values ?? $value)
            );
        }

        return true;
    }
}
