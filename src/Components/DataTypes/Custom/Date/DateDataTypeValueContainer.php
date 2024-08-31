<?php

declare(strict_types=1);

namespace LWP\Components\DataTypes\Custom\Date;

use LWP\Common\Enums\ValidityEnum;
use LWP\Components\DataTypes\Custom\DateTime\DateTimeDataTypeValueContainer;
use LWP\Components\DataTypes\Exceptions\DataTypeError;

class DateDataTypeValueContainer extends DateTimeDataTypeValueContainer
{
    public const DEFAULT_FORMAT = 'Y-m-d';


    public function __construct(
        public mixed $value,
        ?DateDataTypeValueDescriptor $value_descriptor = null
    ) {

        if (!$value_descriptor || $value_descriptor->validity === ValidityEnum::UNDETERMINED) {
            $validator = DateDataType::getValidatorClassObject($value);
        }

        if (
            (isset($validator) && !$validator->validate())
            // One cannot submit invalid values as indicated by descriptor
            || ($value_descriptor && $value_descriptor->validity === ValidityEnum::INVALID)
        ) {
            throw new DataTypeError(sprintf(
                "Given year (%d), month (%d), and day (%d) values do not constitute a valid date.",
                $year,
                $month,
                $day
            ));
        }

        parent::__construct($value, $value_descriptor);
    }
}
