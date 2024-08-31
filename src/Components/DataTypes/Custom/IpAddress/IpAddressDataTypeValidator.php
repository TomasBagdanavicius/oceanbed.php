<?php

declare(strict_types=1);

namespace LWP\Components\DataTypes\Custom\IpAddress;

use LWP\Components\DataTypes\DataTypeValidator;
use LWP\Components\Validators\IpAddressValidator;
use LWP\Network\Exceptions\InvalidIpAddressException;

class IpAddressDataTypeValidator extends DataTypeValidator
{
    public function __construct(
        public mixed $value
    ) {

    }


    //

    public function validate(): bool
    {

        try {
            return (new IpAddressValidator($this->value))->validate();
        } catch (InvalidIpAddressException) {
            return false;
        }
    }
}
