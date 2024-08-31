<?php

declare(strict_types=1);

namespace LWP\Network\Uri;

use LWP\Filesystem\Path\SearchPath;

class UriPathComponent extends SearchPath
{
    public const SEPARATOR = '/';


    public function __construct(
        string $path,
        bool $strict_mode = false,
    ) {

        parent::__construct($path, [self::SEPARATOR], self::SEPARATOR, SearchPath::ALLOW_EMPTY_SEGMENTS);
    }


    // Gets the dirname, and defaults to raw.

    public function getDirname(bool $raw = true): string
    {

        // Raw is required when extracting directory path in HTTP cookies.
        return parent::getDirname($raw);
    }
}
