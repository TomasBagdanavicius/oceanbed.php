<?php

declare(strict_types=1);

namespace LWP\Components\Violations;

use LWP\Common\Array\ArrayCollection;
use LWP\Components\Messages\MessageCollection;

class ViolationCollection extends ArrayCollection
{
    public function __construct(
        array $data = [],
    ) {

        // Allow for valid constraint object classes to be added only.
        parent::__construct(
            element_filter: function (mixed $element): true {

                if (!($element instanceof Violation)) {
                    throw new \Exception(sprintf(
                        "Collection \"%s\" accepts elements of class \"%s\" only.",
                        self::class,
                        Violation::class
                    ));
                }

                return true;
            },
            // Use element class name as the name identifier in the collection.
            obtain_name_filter: function (mixed $element): ?string {

                if ($element instanceof Violation) {
                    return $element::class;
                }

                return null;
            }
        );

        $this->setMass($data);
    }


    // Converts this collection to message collection (MessageCollection).

    public function toErrorMessageCollection(): MessageCollection
    {

        $message_collection = new MessageCollection();

        if ($this->count()) {

            foreach ($this->data as $class_name => $violation) {
                $message_collection->add($violation->getErrorMessage());
            }
        }

        return $message_collection;
    }
}
