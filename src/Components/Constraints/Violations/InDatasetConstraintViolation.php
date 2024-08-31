<?php

declare(strict_types=1);

namespace LWP\Components\Constraints\Violations;

use LWP\Components\Violations\InSetViolation;
use LWP\Components\Constraints\Violations\ConstraintViolationInterface;
use LWP\Components\Constraints\Validators\InDatasetConstraintValidator;

class InDatasetConstraintViolation extends InSetViolation implements ConstraintViolationInterface
{
    public function __construct(
        protected InDatasetConstraintValidator $constraint_validator,
        string|array $value,
        string|array $missing_values
    ) {

        parent::__construct(
            /* Using this instead of `$constraint_validator->constraint->getValue()`, because the latter returns a DatasetInterface and it is not compatible. */
            (array)$value,
            $value,
            $missing_values
        );
    }
}
