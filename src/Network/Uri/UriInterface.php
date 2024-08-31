<?php

declare(strict_types=1);

namespace LWP\Network\Uri;

interface UriInterface
{
    public function setScheme(string $scheme);

    public function getScheme(): string;

}
