<?php

declare(strict_types=1);

namespace LWP\Components\Constraints\Violations;

use LWP\Components\Violations\NotInSetViolation;
use LWP\Components\Constraints\Validators\NotInSetConstraintValidator;

class NotInSetConstraintViolation extends NotInSetViolation implements ConstraintViolationInterface
{
    public function __construct(
        protected NotInSetConstraintValidator $constraint_validator,
        string|array $value,
        string|array $intersecting_values,
    ) {

        parent::__construct(
            $constraint_validator->constraint->getValue(),
            $value,
            $intersecting_values
        );
    }
}
