<?php

declare(strict_types=1);

namespace LWP\Components\Constraints\Violations;

use LWP\Components\Violations\DomainNameViolation;
use LWP\Components\Constraints\Validators\DomainNameConstraintValidator;

class DomainNameConstraintViolation extends DomainNameViolation implements ConstraintViolationInterface
{
    public function __construct(
        protected DomainNameConstraintValidator $constraint_validator,
        string $value,
    ) {

        parent::__construct(
            $constraint_validator->constraint->getValue(),
            $value
        );
    }
}
