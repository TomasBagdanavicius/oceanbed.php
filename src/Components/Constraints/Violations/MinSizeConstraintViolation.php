<?php

declare(strict_types=1);

namespace LWP\Components\Constraints\Violations;

use LWP\Components\Violations\MinSizeViolation;
use LWP\Components\Constraints\Validators\MinSizeConstraintValidator;

class MinSizeConstraintViolation extends MinSizeViolation implements ConstraintViolationInterface
{
    public function __construct(
        protected MinSizeConstraintValidator $constraint_validator,
        int|float $value,
    ) {

        parent::__construct(
            $constraint_validator->constraint->getValue(),
            $value
        );
    }
}
