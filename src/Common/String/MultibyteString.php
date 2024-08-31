<?php

declare(strict_types=1);

namespace LWP\Common\String;

class MultibyteString
{
    public function __construct(
        protected string $string
    ) {

    }


    //

    public function length(): int
    {

        return mb_strlen($this->string);
    }


    //

    public function matches(string $str): bool
    {

        return $this->string === $str;
    }
}
