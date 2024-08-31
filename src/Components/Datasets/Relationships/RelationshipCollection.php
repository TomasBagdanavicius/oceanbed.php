<?php

declare(strict_types=1);

namespace LWP\Components\Datasets\Relationships;

use LWP\Common\Array\RepresentedClassObjectCollection;
use LWP\Common\Collections\ClassObjectCollection;
use LWP\Components\Datasets\Relationships\Relationship;
use LWP\Common\Collections\Exceptions\InvalidMemberException;

class RelationshipCollection extends RepresentedClassObjectCollection implements ClassObjectCollection
{
    public function __construct(
        array $data = []
    ) {

        // Allow for valid constraint object classes to be added only.
        parent::__construct(
            $data,
            element_filter: function (
                mixed $element,
                null|int|string $key
            ): true {

                if (!($element instanceof Relationship)) {
                    throw new InvalidMemberException(sprintf(
                        "Collection %s accepts elements of class %s only",
                        self::class,
                        Relationship::class
                    ));
                }

                return true;
            },
            obtain_name_filter: function (mixed $element): ?string {

                if ($element instanceof Relationship) {
                    return $element->name;
                }

                return null;
            }
        );
    }


    // Creates a new relationship object instance and attaches it to this collection.

    public function createNewMember(array $params = []): Relationship
    {

        $relationship = new Relationship();

        $relationship->registerCollection($this, $this->add($relationship));

        return $relationship;
    }


    //

    public function getNewInstanceArgs(array $data, array $args = []): array
    {

        return [
            'data' => $data
        ];
    }
}
