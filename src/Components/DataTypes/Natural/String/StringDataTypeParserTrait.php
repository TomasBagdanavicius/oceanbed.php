<?php

declare(strict_types=1);

namespace LWP\Components\DataTypes\Natural\String;

trait StringDataTypeParserTrait
{
    protected mixed $value;


    // Gets the string.

    public function getString(): string
    {

        return $this->value;
    }


    // Gets string length. Multibyte safe.

    public function getLength(): int
    {

        return mb_strlen($this->value);
    }


    // Prints the string to the output buffer.

    public function print(): void
    {

        print $this->value;
    }
}
