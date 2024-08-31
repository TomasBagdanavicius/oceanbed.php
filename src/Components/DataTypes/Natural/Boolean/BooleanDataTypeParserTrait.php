<?php

declare(strict_types=1);

namespace LWP\Components\DataTypes\Natural\Boolean;

class BooleanDataTypeParserTrait
{
    //

    public function isTrue(): bool
    {

        return $this->value;
    }


    //

    public function isFalse(): bool
    {

        return !$this->value;
    }
}
