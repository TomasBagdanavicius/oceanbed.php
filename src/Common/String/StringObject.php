<?php

declare(strict_types=1);

namespace LWP\Common\String;

class StringObject implements Stringable
{
    public function __construct(
        protected string $string
    ) {

    }


    //

    public function __toString(): string
    {

        return $this->string;
    }


    //

    public function length(): int
    {

        return strlen($this->string);
    }


    //

    public function trim(): static
    {

        $this->string = trim($this->string);

        return $this;
    }
}
