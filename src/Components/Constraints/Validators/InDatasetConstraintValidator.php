<?php

declare(strict_types=1);

namespace LWP\Components\Constraints\Validators;

use LWP\Components\Constraints\InDatasetConstraint;
use LWP\Components\Constraints\Violations\InDatasetConstraintViolation;
use LWP\Components\DataTypes\Natural\String\StringDataTypeValueContainer;
use LWP\Components\DataTypes\Natural\Integer\IntegerDataTypeValueContainer;

class InDatasetConstraintValidator extends ConstraintValidator implements ConstraintValidatorInterface
{
    public function __construct(
        InDatasetConstraint $constraint
    ) {

        parent::__construct($constraint);
    }


    //

    public function validate(
        null|string|array|StringDataTypeValueContainer|IntegerDataTypeValueContainer $value
    ): true|InDatasetConstraintViolation {

        $dataset = $this->constraint->dataset;
        $condition_group = $this->constraint->condition_group;

        if (is_array($value)) {

            $values = array_unique($value);
            $found_values = $dataset->containsContainerValues(
                $this->constraint->container_name,
                $values,
                $condition_group
            );

            if (count($found_values) !== count($values)) {

                return new InDatasetConstraintViolation(
                    $this,
                    $value,
                    // Multiple missing values.
                    array_diff($values, $found_values)
                );
            }

        } else {

            if ($value !== null) {
                $value = (string)$value;
            }

            $contains_value = $dataset->containsContainerValue(
                $this->constraint->container_name,
                $value,
                $condition_group
            );

            if (!$contains_value) {

                return new InDatasetConstraintViolation(
                    $this,
                    $value,
                    // Single missing value.
                    $value
                );
            }
        }

        return true;
    }
}
