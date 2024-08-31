<?php

declare(strict_types=1);

namespace LWP\Components\Constraints\Validators;

use LWP\Components\Constraints\DomainNameConstraint;
use LWP\Components\Constraints\Violations\DomainNameConstraintViolation;
use LWP\Network\Domain\Domain;
use LWP\Network\EmailAddress;

class DomainNameConstraintValidator extends ConstraintValidator implements ConstraintValidatorInterface
{
    public function __construct(
        DomainNameConstraint $constraint
    ) {

        parent::__construct($constraint);
    }


    // Checks if the given value does not violate the DomainName constraint requirements.

    public function validate(string|Domain|EmailAddress $value): true|DomainNameConstraintViolation
    {

        if ($value instanceof EmailAddress) {
            $value = (string)$value->getDomainPart();
        } elseif ($value instanceof Domain) {
            $value = $value->__toString();
        }

        if ($this->constraint->getValue() != $value) {
            return new DomainNameConstraintViolation($this, $value);
        }

        return true;
    }
}
