<?php

declare(strict_types=1);

namespace LWP\Components\Constraints\Violations;

use LWP\Components\Violations\SizeRangeViolation;
use LWP\Components\Constraints\Validators\SizeRangeConstraintValidator;

class SizeRangeConstraintViolation extends SizeRangeViolation implements ConstraintViolationInterface
{
    public function __construct(
        protected SizeRangeConstraintValidator $constraint_validator,
        int|float|string $value,
    ) {

        parent::__construct(
            $constraint_validator->constraint->getMinSize(),
            $constraint_validator->constraint->getMaxSize(),
            $value,
        );
    }
}
