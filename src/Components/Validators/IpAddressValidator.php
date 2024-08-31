<?php

declare(strict_types=1);

namespace LWP\Components\Validators;

use LWP\Network\Exceptions\InvalidIpAddressException;

class IpAddressValidator extends Validator
{
    public function __construct(
        string $value
    ) {

        parent::__construct($value);
    }


    //

    public function validate(): bool
    {

        if (!filter_var($this->value, FILTER_VALIDATE_IP)) {
            throw new InvalidIpAddressException(sprintf("IP address \"%s\" is invalid", $this->value));
        }

        return true;
    }
}
