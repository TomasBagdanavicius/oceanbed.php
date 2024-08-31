<?php

declare(strict_types=1);

namespace LWP\Components\Properties\Relations;

use LWP\Components\Properties\EnhancedProperty;
use LWP\Components\Properties\Relations\Exceptions\MatchRelationException;
use LWP\Components\Violations\NotEqualToViolation;

class MatchRelation
{
    public function __construct(
        // Property which is dependant upon the related property.
        public readonly EnhancedProperty $primary_property,
        // Source property which provides the value.
        public readonly EnhancedProperty $related_property,
        array $options = [],
    ) {

        if ($primary_property->hasValue() && $related_property->hasValue()) {

            $this->validate(
                $primary_property->getValue(),
                $related_property->getValue()
            );
        }

        $primary_property->onAfterSetValue(
            $this->onPrimaryPropertySetValue(...),
            identifier: 'matching_prime_after_set'
        );

        $related_property->onAfterSetValue(
            $this->onRelatedPropertySetValue(...),
            identifier: 'matching_rel_after_set'
        );
    }


    //

    public function validate(mixed $value1, mixed $value2): void
    {

        if ($value1 != $value2) {

            $violation = new NotEqualToViolation($value1, $value2);

            throw new MatchRelationException(
                "Properties don't match: {$violation->getErrorMessageString()}",
                violation: $violation,
                previous: $violation->getExceptionObject()
            );
        }
    }


    //

    public function onPrimaryPropertySetValue(mixed $value): mixed
    {

        if ($this->related_property->hasValue()) {

            $this->validate($value, $this->related_property->getValue());
        }

        return $value;
    }


    //

    public function onRelatedPropertySetValue(mixed $value): mixed
    {

        if ($this->primary_property->hasValue()) {

            $this->validate($this->primary_property->getValue(), $value);
        }

        return $value;
    }


    //

    public static function removeAssociatedPrimaryPropertyCallbacks(EnhancedProperty $primary_property): void
    {

        $primary_property->unsetOnAfterSetValueCallback('matching_prime_after_set');
    }


    //

    public static function removeAssociatedRelatedPropertyCallbacks(EnhancedProperty $related_property, EnhancedProperty $primary_property): void
    {

        $related_property->unsetOnAfterSetValueCallback('matching_rel_after_set');
    }
}
