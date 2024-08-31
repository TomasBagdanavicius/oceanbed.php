<?php

declare(strict_types=1);

namespace LWP\Components\DataTypes;

use LWP\Components\Constraints\ConstraintCollection;
use LWP\Components\DataTypes\DataTypeValueContainer;
use LWP\Components\Constraints\Constraint;
use LWP\Components\Violations\ViolationCollection;
use LWP\Components\Constraints\Violations\ConstraintViolationInterface;

abstract class DataTypeValueConstraintValidator
{
    public const THROW_ERROR_IMMEDIATELLY = 1;
    public const SUPPRESS_ERROR = 2;


    public function __construct(
        protected DataTypeValueContainer $value_container,
        protected ?ConstraintCollection $constraint_collection = null,
        protected ?int $opts = null,
    ) {

        if (!$constraint_collection) {
            $this->constraint_collection = new ConstraintCollection();
        }
    }


    //

    public function addConstraint(Constraint $constraint): void
    {

        $this->value_container::getDescriptorClassName()::assertConstraint($constraint::class);

        $this->constraint_collection->add($constraint);
    }


    //

    public function validate(): true|ViolationCollection
    {

        if ($this->constraint_collection->count() !== 0) {

            $violation_collection = new ViolationCollection();
            $descriptor_class_name = $this->value_container::getDescriptorClassName();

            foreach ($this->constraint_collection as $constraint_class_name => $constraint) {

                if (($descriptor_class_name)::isSupportedConstraint($constraint_class_name)) {

                    $validator = $constraint->getValidator();
                    $validator_result = $validator->validate($this->value_container);

                    if ($validator_result instanceof ConstraintViolationInterface) {

                        if ($this->opts == self::THROW_ERROR_IMMEDIATELLY) {
                            $validator_result->throwException();
                        } else {
                            $violation_collection->add($validator_result);
                        }
                    }
                }
            }

            return ($violation_collection->count() === 0)
                ? true
                : $violation_collection;
        }

        return true;
    }
}
