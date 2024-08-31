<?php

declare(strict_types=1);

namespace LWP\Components\DataTypes\Natural\Integer;

class IntegerDataTypeBuilder
{
    public static function random(int $length = 12): int
    {

        $min = pow(10, ($length - 1));
        $max = (pow(10, $length) - 1);

        return random_int($min, $max);
    }
}
