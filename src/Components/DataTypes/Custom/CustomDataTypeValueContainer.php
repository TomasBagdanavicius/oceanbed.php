<?php

declare(strict_types=1);

namespace LWP\Components\DataTypes\Custom;

use LWP\Components\DataTypes\DataTypeValueContainer;
use LWP\Components\DataTypes\DataTypeValidator;
use LWP\Components\DataTypes\DataTypeValueDescriptor;

abstract class CustomDataTypeValueContainer extends DataTypeValueContainer
{
    public function __construct(
        mixed $value,
        ?DataTypeValueDescriptor $value_descriptor = null,
        public readonly ?DataTypeValidator $validator = null
    ) {

        parent::__construct($value, $value_descriptor);
    }
}
