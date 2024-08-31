<?php

declare(strict_types=1);

namespace LWP\Components\DataTypes\Natural;

use LWP\Components\DataTypes\DataTypeValueContainer;
use LWP\Components\DataTypes\DataTypeValueConstraintValidator;
use LWP\Components\Constraints\ConstraintCollection;

abstract class NaturalDataTypeValueConstraintValidator extends DataTypeValueConstraintValidator
{
    public function __construct(
        DataTypeValueContainer $value_container,
        ?ConstraintCollection $constraint_collection = null,
        ?int $opts = null,
    ) {

        parent::__construct($value_container, $constraint_collection, $opts);
    }
}
