<?php

declare(strict_types=1);

namespace LWP\Components\Constraints\Validators;

use LWP\Components\Constraints\Constraint;

abstract class ConstraintValidator
{
    public function __construct(
        public readonly Constraint $constraint,
    ) {

    }
}
