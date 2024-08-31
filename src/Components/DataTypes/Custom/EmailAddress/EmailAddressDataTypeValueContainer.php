<?php

declare(strict_types=1);

namespace LWP\Components\DataTypes\Custom\EmailAddress;

use LWP\Common\Enums\ValidityEnum;
use LWP\Components\DataTypes\Custom\CustomDataTypeValueContainer;
use LWP\Components\DataTypes\Exceptions\DataTypeError;
use LWP\Network\Domain\DomainDataReader;

class EmailAddressDataTypeValueContainer extends CustomDataTypeValueContainer
{
    public readonly DomainDataReader $domain_data_reader;


    public function __construct(
        mixed $value,
        ?EmailAddressDataTypeValueDescriptor $value_descriptor = null
    ) {

        if (!isset($GLOBALS['domain_data_reader'])) {
            throw new \RuntimeException("Variable \"domain_data_reader\" should be provided in global context");
        }

        if (!($GLOBALS['domain_data_reader'] instanceof \Closure)) {
            throw new \TypeError(sprintf("Variable \"domain_data_reader\" inside the global context must be an instance of %s", \Closure::class));
        }

        $domain_data_reader = $GLOBALS['domain_data_reader']();

        if (!($domain_data_reader instanceof DomainDataReader)) {
            throw new \TypeError(sprintf(
                "Closure \"domain_data_reader\" inside the global context must return an object that is an instance of %s",
                DomainDataReader::class
            ));
        }

        // Make available in class properties.
        $this->domain_data_reader = $domain_data_reader;

        if (!$value_descriptor || $value_descriptor->validity === ValidityEnum::UNDETERMINED) {

            $validator = EmailAddressDataType::getValidatorClassObject($value, [
                $domain_data_reader
            ]);
        }

        if (
            (isset($validator) && !$validator->validate())
            // One cannot submit invalid values as indicated by descriptor
            || ($value_descriptor && $value_descriptor->validity === ValidityEnum::INVALID)
        ) {
            throw new DataTypeError(sprintf("Value is not of \"%s\" type", EmailAddressDataType::TYPE_NAME));
        }

        parent::__construct($value, $value_descriptor, ($validator ?? null));
    }


    //

    public function __toString(): string
    {

        return $this->value;
    }


    //

    public function getValue(): string // Defines the return data type.
    {return $this->value;
    }
}
