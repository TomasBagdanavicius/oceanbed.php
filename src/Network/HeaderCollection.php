<?php

declare(strict_types=1);

namespace LWP\Network;

use LWP\Common\Common;
use LWP\Common\Collections\ClassObjectCollection;
use LWP\Common\Array\RepresentedClassObjectCollection;

class HeaderCollection extends RepresentedClassObjectCollection implements ClassObjectCollection
{
    public function __construct(
        array $data = []
    ) {

        parent::__construct($data, true, function (mixed $element): true {

            if (!($element instanceof Headers)) {
                Common::throwTypeError(1, __FUNCTION__, Headers::class, gettype($element));
            }

            return true;

        });
    }


    // Creates a new header instance and attaches it to this collection.

    public function createNewMember(array $params = []): Headers
    {

        $headers = new Headers();
        $index = $this->add($headers);
        $headers->registerCollection($this, $index);

        return $headers;
    }


    //

    public function getNewInstanceArgs(array $data, array $args = []): array
    {

        return [
            'data' => $data
        ];
    }
}
