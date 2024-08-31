<?php

declare(strict_types=1);

namespace LWP\Components\Constraints;

use LWP\Common\Array\ArrayCollection;
use LWP\Common\Collections\Exceptions\InvalidMemberException;

class ConstraintCollection extends ArrayCollection
{
    public function __construct(array $data = [])
    {

        // Allow for valid constraint object classes to be added only.
        parent::__construct($data, element_filter: function (mixed $element): true {

            if (!($element instanceof Constraint)) {
                throw new InvalidMemberException(sprintf("Collection %s accepts elements of class %s only", self::class, Constraint::class));
            }

            return true;

            // Use element class name as the name identifier in the collection.
        }, obtain_name_filter: function (mixed $element): ?string {

            if ($element instanceof Constraint) {
                return $element::class;
            }

            return null;

        });
    }


    //

    public function getNewInstanceArgs(array $data, array $args = []): array
    {

        return [
            'data' => $data
        ];
    }
}
