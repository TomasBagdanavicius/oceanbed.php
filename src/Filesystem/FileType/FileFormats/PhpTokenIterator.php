<?php

declare(strict_types=1);

namespace LWP\Filesystem\FileType\FileFormats;

class PhpTokenIterator extends \ArrayIterator
{
    public function __construct(
        string $code,
        int $flags = 0
    ) {

        parent::__construct(
            \PhpToken::tokenize($code),
            $flags
        );
    }
}
