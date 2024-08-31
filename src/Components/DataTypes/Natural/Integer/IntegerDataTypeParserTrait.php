<?php

declare(strict_types=1);

namespace LWP\Components\DataTypes\Natural\Integer;

trait IntegerDataTypeParserTrait
{
    //

    public function getInteger(): int
    {

        return $this->value;
    }


    //

    public function getLength(): int
    {

        return strlen((string)$this->value);
    }


    //

    public function dividesBy(int $integer): bool
    {

        return (($this->value % $integer) == 0);
    }


    //

    public function isSigned(): bool
    {

        return ($this->value < 0);
    }
}
