<?php

declare(strict_types=1);

namespace LWP\Components\Properties\Relations;

use LWP\Components\Properties\EnhancedProperty;
use LWP\Components\DataTypes\ValueOriginEnum;

class AliasRelation
{
    public function __construct(
        // Property which is dependant upon the related property.
        private EnhancedProperty $primary_property,
        // Source property which provides the value.
        private EnhancedProperty $related_property,
        array $options = []
    ) {

        $this->can($related_property);

        if (!$primary_property->hasValue() && $related_property->hasValue()) {

            $primary_property->setValue(
                $related_property->getValue(),
                // This indicates that value was set through relational dependency.
                value_origin: ValueOriginEnum::INTERNAL
            );
        }

        $primary_property->onAfterGetValue(
            $this->onPrimaryPropertyGetValue(...),
            identifier: 'alias_prime_after_get'
        );

        $related_property->onAfterSetValue(
            $this->onRelatedPropertySetValue(...),
            identifier: 'alias_rel_after_set'
        );
    }


    // Gets the primary property object.

    public function getPrimaryProperty(): EnhancedProperty
    {

        return $this->primary_property;
    }


    // Gets the related property object.

    public function getRelatedProperty(): EnhancedProperty
    {

        return $this->related_property;
    }


    // Tells if relation can be made to a given property.

    public function can(EnhancedProperty $property): void
    {

        if ($this->primary_property === $property) {
            throw new \Exception(
                "Related property must be different from the primary property."
            );
        }
    }


    // Filters primary property's getter value.

    private function onPrimaryPropertyGetValue(mixed $value): mixed
    {

        if (!($value instanceof \Throwable)) {

            return $value;

        } else {

            if ($this->related_property->hasValue()) {
                return $this->related_property->getValue();
            } else {
                throw $value;
            }
        }
    }


    // Filters related property's setter value.

    private function onRelatedPropertySetValue(mixed $value): mixed
    {

        if (
            !$this->primary_property->hasValue()
            // If value was set, check if it is not an OWN value, eg. was set through relational dependency.
            || $this->primary_property->getValueOrigin() === ValueOriginEnum::INTERNAL
        ) {

            $this->primary_property->setValue(
                $value,
                value_origin: ValueOriginEnum::INTERNAL
            );
        }

        return $value;
    }


    //

    public static function removeAssociatedPrimaryPropertyCallbacks(EnhancedProperty $primary_property): void
    {

        $primary_property->unsetOnAfterSetValueCallback('alias_prime_after_get');
    }


    //

    public static function removeAssociatedRelatedPropertyCallbacks(EnhancedProperty $related_property, EnhancedProperty $primary_property): void
    {

        $related_property->unsetOnAfterSetValueCallback('alias_rel_after_set');
    }
}
