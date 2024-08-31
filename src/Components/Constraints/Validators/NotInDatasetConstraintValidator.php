<?php

declare(strict_types=1);

namespace LWP\Components\Constraints\Validators;

use LWP\Components\Constraints\NotInDatasetConstraint;
use LWP\Components\Constraints\Violations\NotInDatasetConstraintViolation;
use LWP\Components\DataTypes\Natural\String\StringDataTypeValueContainer;
use LWP\Components\DataTypes\Natural\Integer\IntegerDataTypeValueContainer;
use LWP\Components\DataTypes\Custom\Number\NumberDataTypeValueContainer;

class NotInDatasetConstraintValidator extends ConstraintValidator implements ConstraintValidatorInterface
{
    public function __construct(
        NotInDatasetConstraint $constraint,
    ) {

        parent::__construct($constraint);
    }


    //

    public function validate(
        null|string|StringDataTypeValueContainer|IntegerDataTypeValueContainer|NumberDataTypeValueContainer|array $value
    ): true|NotInDatasetConstraintViolation {

        $dataset = $this->constraint->dataset;
        $condition_group = $this->constraint->condition_group;

        if (is_array($value)) {

            $values = array_unique($value);
            $found_values = $dataset->containsContainerValues(
                $this->constraint->container_name,
                $values,
                $condition_group
            );

            // If any of the values were found, it's a violation.
            if (count($found_values)) {

                return new NotInDatasetConstraintViolation(
                    $this,
                    $value,
                    // Intersecting values.
                    $found_values
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

            if ($contains_value) {

                return new NotInDatasetConstraintViolation(
                    $this,
                    $value,
                    // Intersecting value.
                    $value
                );
            }
        }

        return true;
    }
}
