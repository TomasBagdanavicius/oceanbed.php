<?php

declare(strict_types=1);

namespace LWP\Components\DataTypes\Natural\String;

use LWP\Common\String\Str;

class StringDataTypeBuilder
{
    public static function random(int $length = 32, string $charlist = '0-9a-zA-ZSP'): string
    {

        return Str::random($length, $charlist);
    }
}
