<?php

declare(strict_types=1);

namespace LWP\Components\DataTypes\Custom\Number;

use LWP\Common\Enums\ValidityEnum;
use LWP\Common\Interfaces\Sizeable;
use LWP\Components\DataTypes\Custom\CustomDataTypeValueContainer;
use LWP\Components\Rules\NumberFormattingRule;
use LWP\Components\DataTypes\Exceptions\DataTypeError;
use LWP\Components\DataTypes\Interfaces\ConstructorAcceptsFormattingRuleInterface;

class NumberDataTypeValueContainer extends CustomDataTypeValueContainer implements Sizeable, \Stringable, ConstructorAcceptsFormattingRuleInterface
{
    public function __construct(
        mixed $value,
        ?NumberDataTypeValueDescriptor $value_descriptor = null,
        private ?NumberFormattingRule $formatting_rule = null
    ) {

        if (!$value_descriptor || $value_descriptor->validity === ValidityEnum::UNDETERMINED) {

            $validator = NumberDataType::getValidatorClassObject($value, [
                $formatting_rule,
            ]);
        }

        if (
            (isset($validator) && !$validator->validate())
            // One cannot submit invalid values as indicated by descriptor
            || ($value_descriptor && $value_descriptor->validity === ValidityEnum::INVALID)
        ) {
            throw new DataTypeError(sprintf("Value is not of \"%s\" type.", NumberDataType::TYPE_NAME));
        }

        parent::__construct($value, $value_descriptor, ($validator ?? null));
    }


    //

    public function __toString(): string
    {

        return $this->value;
    }


    //

    public function getValue(): string
    {

        return $this->value;
    }


    //

    public function getParser(): NumberDataTypeParser
    {

        if (!$this->parser) {
            $this->parser = new (self::getGenericParserClassName())((string)$this->value, $this->formatting_rule);
        }

        return $this->parser;
    }


    //

    public function getSize(): float
    {

        return $this->getParser()->getFloat();
    }


    //

    public static function getConvertFormattingRuleClassName(): ?string
    {

        return NumberFormattingRule::class;
    }
}
