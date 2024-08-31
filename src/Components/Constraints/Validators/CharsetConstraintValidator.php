<?php

declare(strict_types=1);

namespace LWP\Components\Constraints\Validators;

use LWP\Components\Constraints\CharsetConstraint;
use LWP\Components\Constraints\Violations\CharsetConstraintViolation;

class CharsetConstraintValidator extends ConstraintValidator implements ConstraintValidatorInterface
{
    public function __construct(
        CharsetConstraint $constraint,
    ) {

        parent::__construct($constraint);
    }


    // Checks if the given value does not violate the charset constraint requirements.

    public function validate(mixed $value): true|CharsetConstraintViolation
    {

        switch ($this->constraint->getValue()) {

            case 'ascii':

                if (!mb_check_encoding($value, 'ASCII')) {
                    return new CharsetConstraintViolation($this, $value);
                }

                break;
        }

        return true;
    }
}
