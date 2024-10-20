<?php

declare(strict_types=1);

namespace LWP\Components\Constraints\Violations;

use LWP\Components\Violations\InSetViolation;
use LWP\Components\Constraints\Validators\InSetConstraintValidator;

class InSetConstraintViolation extends InSetViolation implements ConstraintViolationInterface
{
    public function __construct(
        protected InSetConstraintValidator $constraint_validator,
        string|int|array $value,
        string|int|array $missing_values,
    ) {

        parent::__construct(
            $constraint_validator->constraint->getValue(),
            $value,
            $missing_values
        );
    }
}
