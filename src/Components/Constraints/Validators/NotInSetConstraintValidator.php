<?php

declare(strict_types=1);

namespace LWP\Components\Constraints\Validators;

use LWP\Components\Constraints\NotInSetConstraint;
use LWP\Components\Constraints\Violations\NotInSetConstraintViolation;
use LWP\Components\DataTypes\Natural\String\StringDataTypeValueContainer;
use LWP\Components\DataTypes\Natural\Integer\IntegerDataTypeValueContainer;

class NotInSetConstraintValidator extends ConstraintValidator implements ConstraintValidatorInterface
{
    public function __construct(
        NotInSetConstraint $constraint,
    ) {

        parent::__construct($constraint);
    }


    //

    public function validate(
        string|StringDataTypeValueContainer|array|IntegerDataTypeValueContainer|int $value
    ): true|NotInSetConstraintViolation {

        $set = $this->constraint->getSet();

        if ($value instanceof StringDataTypeValueContainer) {
            $value = $value->getValue();
        }

        if (
            (is_string($value) && in_array($value, $set))
            // Checks if all elements in the value array are not present in the set array.
            || (is_array($value) && array_diff($value, $set) !== $value)
        ) {

            return new NotInSetConstraintViolation(
                $this,
                $value,
                // Intersecting value(s).
                (is_string($value)
                    ? $value
                    : array_intersect($value, $set))
            );
        }

        return true;
    }
}
