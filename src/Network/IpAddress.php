<?php

declare(strict_types=1);

namespace LWP\Network;

use LWP\Network\Exceptions\InvalidIpAddressException;
use LWP\Components\Validators\IpAddressValidator;

class IpAddress implements \Stringable
{
    public function __construct(
        public readonly string $ip_address_str
    ) {

        $validator = new IpAddressValidator($ip_address_str);

        // `validate()` will throw, but leaving exception here for completeness
        if (!$validator->validate()) {
            throw new InvalidIpAddressException(sprintf(
                "IP address \"%s\" is invalid",
                $ip_address_str
            ));
        }
    }


    // Gets string representation.

    public function __toString(): string
    {

        return $this->ip_address_str;
    }


    // Creates a new instance from a long integer address representing an IP address.

    public static function fromLong(int $long): self
    {

        if (!$ip_address = long2ip($long)) {
            throw new Exceptions\LongIsNotAnIpAddressException(
                "Long \"$long\" does not represent an IP address."
            );
        }

        return new static($ip_address);
    }


    // Creates a new instance from a hexadecimal representing an IP address.

    public static function fromHexadecimal(string $value): self
    {

        if (!ctype_xdigit($value)) {
            throw new \TypeError(
                "Provided value is not a hexidecimal."
            );
        }

        if (!$value = hex2bin($value)) {
            throw new \RuntimeException(
                "Could not convert hexadecimal to bbinary."
            );
        }

        if (!$value = inet_ntop($value)) {
            throw new Exceptions\HexadecimalIsNotAnIpAddressException(
                "Hexadecimal \"$value\" does not represent an IP addresss."
            );
        }

        return new static($value);
    }


    // Gets IP address version.

    public function getVersion(): int
    {

        // Can not rely on dot (".") or colon (":") detection methods, because valid IPv4 can contain colons, and valid IPv6 can contain dots.
        return (filter_var($this->ip_address_str, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4))
            ? 4
            : 6;
    }


    // Gets long integer representation for the IPv4 version.

    public function getLong(): ?int
    {

        return ($this->getVersion() === 4)
            ? ip2long($this->ip_address_str)
            : null;
    }


    // Gets binary representation.

    public function getBinary(): string
    {

        // Assuming that the ip address is always correct, because it was validated in constructor. Would throw an exception otherwise.
        return inet_pton($this->ip_address_str);
    }


    // Gets hexadecimal representation.

    public function getHexadecimal(): string
    {

        return bin2hex($this->getBinary());
    }
}
