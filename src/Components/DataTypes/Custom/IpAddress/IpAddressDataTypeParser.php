<?php

declare(strict_types=1);

namespace LWP\Components\DataTypes\Custom\IpAddress;

use LWP\Network\IpAddress;

class IpAddressDataTypeParser extends IpAddress
{
    public function __construct(
        # Class type vs regular type.
        public readonly IpAddressDataTypeValueContainer $ip_address_value,
    ) {

        parent::__construct($ip_address_value->__toString());
    }
}
