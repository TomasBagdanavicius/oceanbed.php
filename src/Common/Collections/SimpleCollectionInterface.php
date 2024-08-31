<?php

declare(strict_types=1);

namespace LWP\Common\Collections;

interface SimpleCollection extends \Countable, \IteratorAggregate, \ArrayAccess
{
    public function add(Collectable $element);

}
