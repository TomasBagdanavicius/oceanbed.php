<?php

declare(strict_types=1);

namespace LWP\Components\Properties;

use LWP\Common\Common;
use LWP\Common\Array\RepresentedClassObjectCollection;
use LWP\Common\Collections\ClassObjectCollection;
use LWP\Common\Exceptions\DuplicateException;

abstract class AbstractPropertyCollection extends RepresentedClassObjectCollection implements ClassObjectCollection
{
    public function __construct(
        public readonly string $accepted_class_name,
        array $data = [],
    ) {

        parent::__construct(
            $data,
            two_level_tree_support: true,
            element_filter: function (object $element): true {

                if ($element::class !== $this->accepted_class_name) {

                    $element_type = gettype($element);

                    Common::throwTypeError(1, __FUNCTION__, $this->accepted_class_name, (($element_type === 'object')
                        ? $element::class
                        : $element_type));
                }

                if ($this->containsKey($element->property_name)) {

                    // The policy is to inform about duplicates instead of override them.
                    throw new DuplicateException(sprintf(
                        "Property named \"%s\" already exists in the %s collection.",
                        $element->property_name,
                        static::class
                    ));
                }

                return true;
            },
            obtain_name_filter: function (object $element): ?string {

                if ($element::class === $this->accepted_class_name) {
                    return $element->property_name;
                }

                return null;
            }
        );
    }


    // Builds a new property object class with the given parameters.

    public function createNewMember(array $params = []): BaseProperty
    {

        $property = new ($this->accepted_class_name)(...$params);

        // Relational properties are added automatically.
        if ($property instanceof RelationalProperty) {
            $index_number = $this->add($property);
        }

        $property->registerCollection($this, $index_number);

        return $property;
    }
}
