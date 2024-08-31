<?php

declare(strict_types=1);

namespace LWP\Components\Validators;

use LWP\Network\EmailAddress;
use LWP\Network\Domain\DomainDataReader;
use LWP\Network\Domain\Exceptions\InvalidDomainException;
use LWP\Network\Exceptions\InvalidEmailAddressLocalPartException;
use LWP\Network\Exceptions\InvalidEmailAddressException;

class EmailAddressValidator extends Validator
{
    public function __construct(
        string $value,
        public readonly DomainDataReader $domain_data_reader
    ) {

        parent::__construct($value);
    }


    // Validates the current set value.

    public function validate(): bool
    {

        try {
            $email_address = new EmailAddress($this->value, EmailAddress::DOMAIN_VALIDATE_AS_PUBLIC, $this->domain_data_reader);
            return true;
            # Other class types might need to be added.
        } catch (InvalidDomainException|InvalidEmailAddressLocalPartException|InvalidEmailAddressException $exception) {
            throw new InvalidEmailAddressException("Email address \"{$this->value}\" is invalid: {$exception->getMessage()}");
        }
    }
}
