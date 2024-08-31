<?php

declare(strict_types=1);

namespace LWP\Network\Http;

use LWP\Common\OptionManager;

interface RequestInterface
{
    public function getId(): string;

    public function getOptions(): OptionManager;

}
