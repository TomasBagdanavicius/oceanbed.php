<?php

declare(strict_types=1);

namespace LWP\Common\Collections;

use LWP\Common\Collectable;

interface ClassObjectCollection
{
    //

    public function createNewMember(array $params = []): Collectable;

}
