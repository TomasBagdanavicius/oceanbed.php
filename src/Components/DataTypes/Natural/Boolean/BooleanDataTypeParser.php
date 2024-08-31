<?php

declare(strict_types=1);

namespace LWP\Components\DataTypes\Natural\Boolean;

class BooleanDataTypeParser
{
    use BooleanDataTypeParserTrait;


    public function __construct(
        private BooleanDataTypeValueContainer $value_container,
    ) {

        $this->value = $value_container->getValue();
    }
}
