<?php

declare(strict_types=1);

namespace LWP\Components\DataTypes\Natural\String;

use LWP\Components\DataTypes\DataTypeValueConstraintValidator;
use LWP\Components\Constraints\ConstraintCollection;
use LWP\Components\DataTypes\Natural\NaturalDataTypeValueConstraintValidator;

class StringDataTypeValueConstraintValidator extends NaturalDataTypeValueConstraintValidator
{
    public function __construct(
        StringDataTypeValueContainer $value_container,
        ?ConstraintCollection $constraint_collection = null,
        ?int $opts = DataTypeValueConstraintValidator::THROW_ERROR_IMMEDIATELLY,
    ) {

        parent::__construct($value_container, $constraint_collection, $opts);
    }
}
