<?php

declare(strict_types=1);

namespace LWP\Components\DataTypes\Custom\EmailAddress;

use LWP\Network\EmailAddress;
use LWP\Network\Domain\DomainDataReader;

class EmailAddressDataTypeParser extends EmailAddress
{
    public function __construct(
        # Class type vs regular type.
        public readonly EmailAddressDataTypeValueContainer $email_address_value,
        public readonly DomainDataReader $domain_data_reader,
    ) {

        parent::__construct(
            email_address: $email_address_value->__toString(),
            domain_validate_method: parent::DOMAIN_VALIDATE_AS_PUBLIC,
            domain_data_reader: $domain_data_reader,
        );
    }
}
