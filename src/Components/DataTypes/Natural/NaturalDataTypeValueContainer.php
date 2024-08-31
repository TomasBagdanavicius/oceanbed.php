<?php

declare(strict_types=1);

namespace LWP\Components\DataTypes\Natural;

use LWP\Components\DataTypes\DataTypeValueContainer;
use LWP\Components\DataTypes\DataTypeValueDescriptor;
use LWP\Components\DataTypes\DataTypeValidator;

abstract class NaturalDataTypeValueContainer extends DataTypeValueContainer
{
    public function __construct(
        mixed $value,
        ?DataTypeValueDescriptor $value_descriptor = null,
        public readonly ?DataTypeValidator $validator = null
    ) {

        parent::__construct($value, $value_descriptor);
    }
}
