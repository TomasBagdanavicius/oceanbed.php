<?php

declare(strict_types=1);

namespace LWP\Components\DataTypes\Custom\EmailAddress;

use LWP\Network\Domain\DomainDataReader;
use LWP\Components\DataTypes\DataTypeValidator;
use LWP\Components\Validators\EmailAddressValidator;
use LWP\Network\Exceptions\InvalidEmailAddressException;

class EmailAddressDataTypeValidator extends DataTypeValidator
{
    public function __construct(
        public mixed $value,
        public readonly DomainDataReader $domain_data_reader
    ) {

    }


    //

    public function validate(): bool
    {

        try {
            return (new EmailAddressValidator($this->value, $this->domain_data_reader))->validate();
        } catch (InvalidEmailAddressException) {
            return false;
        }
    }
}
