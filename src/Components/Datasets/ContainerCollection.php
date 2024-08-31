<?php

declare(strict_types=1);

namespace LWP\Components\Datasets;

use LWP\Common\Array\RepresentedClassObjectCollection;
use LWP\Common\Collections\ClassObjectCollection;
use LWP\Common\Collections\Exceptions\InvalidMemberException;

class ContainerCollection extends RepresentedClassObjectCollection implements ClassObjectCollection
{
    public function __construct(array $data = [])
    {

        // Allow for valid constraint object classes to be added only.
        parent::__construct(
            $data,
            element_filter: function (
                mixed $element,
                null|int|string $key
            ): true {

                if (!($element instanceof Container)) {
                    throw new InvalidMemberException(sprintf(
                        "Collection %s accepts elements of class %s only",
                        self::class,
                        Container::class
                    ));
                }

                return true;
            },
            obtain_name_filter: function (mixed $element): ?string {

                if ($element instanceof Container) {
                    return $element->container_name;
                }

                return null;
            }
        );
    }


    //

    public function createNewMember(array $params = []): Container
    {

        $container = new Container();
        $container->registerCollection($this, $this->add($container));

        return $container;
    }


    //

    public function getNewInstanceArgs(array $data, array $args = []): array
    {

        return [
            'data' => $data
        ];
    }
}
