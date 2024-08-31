<?php

declare(strict_types=1);

namespace LWP\Components\Constraints\Violations;

use LWP\Components\Violations\CharsetViolation;
use LWP\Components\Constraints\Validators\CharsetConstraintValidator;

class CharsetConstraintViolation extends CharsetViolation implements ConstraintViolationInterface
{
    public function __construct(
        protected CharsetConstraintValidator $constraint_validator,
        string $value,
    ) {

        parent::__construct(
            $constraint_validator->constraint->getValue(),
            $value,
            $constraint_validator->constraint::ACCEPTED_CHARSETS
        );
    }
}
