<?php

declare(strict_types=1);

namespace LWP\Components\DataTypes\Custom\DateTime;

use LWP\Common\Enums\ValidityEnum;
use LWP\Common\Interfaces\Sizeable;
use LWP\Components\DataTypes\Custom\CustomDataTypeValueContainer;
use LWP\Components\Rules\DateTimeFormattingRule;
use LWP\Components\DataTypes\Exceptions\DataTypeError;
use LWP\Components\DataTypes\Interfaces\ConstructorAcceptsFormattingRuleInterface;

class DateTimeDataTypeValueContainer extends CustomDataTypeValueContainer implements Sizeable, \Stringable, ConstructorAcceptsFormattingRuleInterface
{
    #review: Is this a good location?
    public const DEFAULT_FORMAT = 'Y-m-d H:i:s';


    public function __construct(
        mixed $value,
        ?DateTimeDataTypeValueDescriptor $value_descriptor = null,
        private ?DateTimeFormattingRule $formatting_rule = null
    ) {

        if (!$value_descriptor || $value_descriptor->validity === ValidityEnum::UNDETERMINED) {
            $validator = DateTimeDataType::getValidatorClassObject($value, [
                $formatting_rule
            ]);
        }

        if (
            (isset($validator) && !$validator->validate())
            // One cannot submit invalid values as indicated by descriptor
            || ($value_descriptor && $value_descriptor->validity === ValidityEnum::INVALID)
        ) {
            throw new DataTypeError(sprintf(
                "Value \"%s\" is not of \"%s\" type",
                $value,
                DateTimeDataType::TYPE_NAME
            ));
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

        return (string)$this->value;
    }


    //

    public function getSize(): int
    {

        return $this->getParser()->getTimestamp();
    }


    //

    public static function getConvertFormattingRuleClassName(): ?string
    {

        return DateTimeFormattingRule::class;
    }
}
