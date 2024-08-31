<?php

declare(strict_types=1);

namespace LWP\Components\Constraints\Violations;

use LWP\Components\Violations\NotInSetViolation;
use LWP\Components\Constraints\Validators\NotInDatasetConstraintValidator;

class NotInDatasetConstraintViolation extends NotInSetViolation implements ConstraintViolationInterface
{
    public function __construct(
        protected NotInDatasetConstraintValidator $constraint_validator,
        string|array $value,
        string|array $intersecting_values,
    ) {

        parent::__construct(
            /* Using this instead of `$constraint_validator->constraint->getValue()`, because the latter returns a DatasetInterface and it is not compatible. */
            (array)$value,
            $value,
            $intersecting_values
        );
    }
}
