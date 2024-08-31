<?php

declare(strict_types=1);

namespace LWP\Components\Properties\Relations;

use LWP\Components\Violations\EqualToViolation;
use LWP\Components\Properties\Relations\Exceptions\MismatchRelationException;

class MismatchRelation extends MatchRelation
{
    //

    public function validate(mixed $value1, mixed $value2): void
    {

        if ($value1 == $value2) {

            $violation = new EqualToViolation($value1, $value2);

            throw new MismatchRelationException(
                "Properties must not match: {$violation->getErrorMessageString()}",
                violation: $violation,
                previous: $violation->getExceptionObject()
            );
        }
    }
}
