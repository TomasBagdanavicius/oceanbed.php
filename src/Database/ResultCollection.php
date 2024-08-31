<?php

declare(strict_types=1);

namespace LWP\Database;

use LWP\Common\Array\ArrayCollection;
use LWP\Common\Collections\Exceptions\InvalidMemberException;

class ResultCollection extends ArrayCollection
{
    public function __construct()
    {

        parent::__construct(element_filter: function (mixed $element): true {

            if (!($element instanceof Result)) {
                throw new InvalidMemberException(sprintf("Collection %s accepts elements of class %s only", self::class, Result::class));
            }

            return true;

        });
    }
}
