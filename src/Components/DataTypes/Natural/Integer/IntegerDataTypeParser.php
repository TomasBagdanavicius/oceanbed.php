<?php

declare(strict_types=1);

namespace LWP\Components\DataTypes\Natural\Integer;

class IntegerDataTypeParser
{
    use IntegerDataTypeParserTrait;


    public readonly int $value;


    public function __construct(
        private IntegerDataTypeValueContainer $value_container,
    ) {

        $this->value = $value_container->getValue();
    }
}
