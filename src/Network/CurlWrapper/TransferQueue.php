<?php

declare(strict_types=1);

namespace LWP\Network\CurlWrapper;

use LWP\Common\Array\ArrayCollection;

class TransferQueue extends ArrayCollection
{
    public function __construct(array $data = [])
    {

        parent::__construct($data);
    }
}
