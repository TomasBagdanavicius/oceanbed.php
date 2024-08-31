<?php

declare(strict_types=1);

namespace LWP\Components\DataTypes\Natural\Integer;

use LWP\Components\DataTypes\Natural\NaturalDataTypeValueContainer;
use LWP\Common\Interfaces\Sizeable;
use LWP\Common\Exceptions\Math\NumberNotDivisibleByException;
use LWP\Components\DataTypes\Exceptions\DataTypeError;
use LWP\Common\Enums\ValidityEnum;
use LWP\Components\DataTypes\Custom\Number\NumberDataTypeValueContainer;
use LWP\Components\DataTypes\DataTypeValueContainer;

class IntegerDataTypeValueContainer extends NaturalDataTypeValueContainer implements Sizeable, \Stringable
{
    public function __construct(
        mixed $value,
        ?IntegerDataTypeValueDescriptor $value_descriptor = null
    ) {

        if (!$value_descriptor || $value_descriptor->validity === ValidityEnum::UNDETERMINED) {
            $validator = IntegerDataType::getValidatorClassObject($value);
        }

        if (
            (isset($validator) && !$validator->validate())
            // One cannot submit invalid values as indicated by descriptor
            || ($value_descriptor && $value_descriptor->validity === ValidityEnum::INVALID)
        ) {

            throw new DataTypeError(sprintf(
                "Value is not of \"%s\" type.",
                IntegerDataType::TYPE_NAME
            ));
        }

        parent::__construct($value, $value_descriptor, ($validator ?? null));
    }


    //

    public function __toString(): string
    {

        return (string)$this->value;
    }


    //

    public function modifyByDeduction(int $integer): self
    {

        return new self($this->value - $integer);
    }


    //

    public function modifyByAddition(int $integer): self
    {

        return new self($this->value + $integer);
    }


    //

    public function modifyByDivision(int $integer, bool $return_float = false): self
    {

        if (!$this->dividesBy($integer)) {

            if (!$return_float) {

                throw new NumberNotDivisibleByException(sprintf(
                    "Provided integer ($integer) does not divide by \"$this->value\".",
                ));

            } else {

                return new NumberDataTypeValueContainer($this->value / $integer);
            }
        }

        return new self($this->value / $integer);
    }


    //

    public function modifyByUnity(int $integer): self
    {

        return new self($this->value + $integer);
    }


    // Defines custom return type for strictness.

    public function getValue(): int
    {

        return $this->value;
    }


    //

    public function getSize(): int
    {

        return $this->value;
    }
}
