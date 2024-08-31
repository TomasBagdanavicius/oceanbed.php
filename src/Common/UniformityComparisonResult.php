<?php

declare(strict_types=1);

namespace LWP\Common;

class UniformityComparisonResult
{
    public const LOWER = 0;
    public const EQUAL = 1;
    public const HIGHER = 2;


    public function __construct(
        private int $result,
    ) {

    }


    //

    public function isLower(): bool
    {

        return ($this->result === self::LOWER);
    }


    //

    public function isEqual(): bool
    {

        return ($this->result === self::EQUAL);
    }


    //

    public function isHigher(): bool
    {

        return ($this->result === self::HIGHER);
    }
}
