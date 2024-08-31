<?php

declare(strict_types=1);

namespace LWP\Components\DataTypes\Custom\Number;

use LWP\Components\DataTypes\Custom\CustomDataTypeValueConstraintValidator;
use LWP\Components\Constraints\ConstraintCollection;
use LWP\Components\DataTypes\DataTypeValueConstraintValidator;

class NumberDataTypeValueConstraintValidator extends CustomDataTypeValueConstraintValidator
{
    public function __construct(
        NumberDataTypeValueContainer $value_container,
        ?ConstraintCollection $constraint_collection = null,
        ?int $opts = DataTypeValueConstraintValidator::THROW_ERROR_IMMEDIATELLY,
    ) {

        parent::__construct($value_container, $constraint_collection, $opts);
    }
}
