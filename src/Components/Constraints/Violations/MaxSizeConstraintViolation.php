<?php

declare(strict_types=1);

namespace LWP\Components\Constraints\Violations;

use LWP\Components\Violations\MaxSizeViolation;
use LWP\Components\Constraints\Validators\MaxSizeConstraintValidator;

class MaxSizeConstraintViolation extends MaxSizeViolation implements ConstraintViolationInterface
{
    public function __construct(
        protected MaxSizeConstraintValidator $constraint_validator,
        int|float|string $value,
    ) {

        parent::__construct(
            $constraint_validator->constraint->getValue(),
            $value
        );
    }
}
