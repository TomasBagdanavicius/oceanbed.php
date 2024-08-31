<?php

declare(strict_types=1);

namespace LWP\Components\DataTypes\Natural\String;

use LWP\Components\DataTypes\Natural\String\StringDataTypeValueContainer;

class StringDataTypeParser
{
    use StringDataTypeParserTrait;


    public function __construct(
        private StringDataTypeValueContainer $value_container,
    ) {

        $this->value = $value_container->getValue();
    }
}
