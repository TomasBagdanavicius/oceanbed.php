<?php

declare(strict_types=1);

namespace LWP\Network;

use InvalidArgumentException;
use LWP\Common\String\EnclosedCharsIterator;
use LWP\Network\Domain\Domain;
use LWP\Network\Domain\DomainDataReader;
use LWP\Network\Hostname;
use LWP\Network\IpAddress;
use LWP\Network\Exceptions\InvalidEmailAddressException;
use LWP\Network\Exceptions\InvalidEmailAddressLocalPartException;
use LWP\Common\Exceptions\EmptyElementException;
use LWP\Network\Domain\Exceptions\InvalidDomainException;

class EmailAddress implements \Stringable
{
    public const SEPARATOR = '@'; // Local-part and domain separator.

    public const DOMAIN_VALIDATE_NONE = 0; // Do not validate the domain name at all.
    public const DOMAIN_VALIDATE_AS_HOSTNAME = 1; // Validate domain as a host name.
    public const DOMAIN_VALIDATE_AS_PUBLIC = 2; // Validate domain name as public domain name.

    private bool $is_ip_address;
    private string $local_part = '';
    private array $local_part_comments = [];
    private bool $is_local_part_quoted;
    private string|Hostname|Domain|IpAddress $domain = '';
    private array $domain_comments = [];


    /* The decision to perform full validation in the constructor method was made in order to simplify validation.
    Otherwise a special method that would check if all parts have been added would be required. Also, if a string
    with no separator is submitted, it's unclear whether it should be considered the local-part of the domain.
    Finally, an email address builder is something that might be rarely used. */
    public function __construct(
        string $email_address,
        private int $domain_validate_method = self::DOMAIN_VALIDATE_AS_HOSTNAME,
        private ?DomainDataReader $domain_data_reader = null,
    ) {

        if ($email_address === '') {
            throw new \ValueError("Email address must not be empty");
        }

        // This method rests upon the assumption that the safest and simpliest way is to split at the last occurence of the separator.
        # Which might be not enough when comments are used inside the domain part, and they contain the @ char. But those are super rare cases.
        $separator_pos = strrpos($email_address, self::SEPARATOR);

        if ($separator_pos === false) {
            throw new InvalidEmailAddressException(sprintf(
                "Email address is missing the \"%s\" separator",
                self::SEPARATOR
            ));
        }

        $local_part_str = substr($email_address, 0, $separator_pos);

        if ($local_part_str === '') {
            throw new InvalidEmailAddressLocalPartException("Email address' local part must not be empty");
        }

        $this->setLocalPart($local_part_str);

        $domain_str = substr($email_address, ($separator_pos + 1));

        if ($domain_str === '') {
            throw new InvalidDomainException("Domain name must not be empty");
        }

        $this->setDomain($domain_str);
    }


    // Gets the real email address string. Will omit comments.

    public function __toString(): string
    {

        $result = ($this->local_part . self::SEPARATOR);

        if ($this->is_ip_address) {
            $result .= '[';
        }

        $result .= $this->domain;

        if ($this->is_ip_address) {
            $result .= ']';
        }

        return $result;
    }


    // Sets the domain name validation method.

    public function setDomainValidateMethod(int $domain_validate_method): void
    {

        $this->domain_validate_method = $domain_validate_method;
    }


    // Gets the current domain name validation method.

    public function getDomainValidateMethod(): int
    {

        return $this->domain_validate_method;
    }


    // Tells is domain part is an IP address.

    public function isIpAddress(): bool
    {

        return $this->is_ip_address;
    }


    // Validates and sets the local part string.

    public function setLocalPart(string $local_part): void
    {

        if ($local_part == '') {

            throw new \Exception("Local-part cannot be empty.");
        }

        $local_part_data = self::validateLocalPart($local_part);

        $local_part = $local_part_data['contents'];

        if ($local_part_data['is_quoted']) {
            $local_part = ('"' . $local_part . '"');
        }

        $this->local_part = $local_part;
        $this->local_part_comments = $local_part_data['comments'];
        $this->is_local_part_quoted = $local_part_data['is_quoted'];
    }


    // Gets the local part. Default is an empty string.

    public function getLocalPart(): string
    {

        return $this->local_part;
    }


    // Gets local part comments.

    public function getLocalPartComments(): array
    {

        return $this->local_part_comments;
    }


    // Sets the domain part. Default is an empty string.

    public function setDomain(string $domain): void
    {

        if ($domain === '') {
            throw new \ValueError("Domain cannot be empty.");
        }

        // Comments are allowed in the domain as well as in the local-part.
        $domain_data = self::extractPartComments($domain);
        $this->domain_comments = $domain_data['comments'];

        $domain = implode($domain_data['segments']);

        // Expecting anything else, but an IP address.
        // The presumption here is that if domain part starts with a "[" and ends with a "]", it must be an IP address inside.
        if (!($domain[0] == '[' && substr($domain, -1) == ']')) {

            // Validates as a hostname.
            if ($this->domain_validate_method == self::DOMAIN_VALIDATE_AS_HOSTNAME) {

                $this->setFromHostname(new Hostname($domain));

                // Validates as a domain name.
            } elseif ($this->domain_validate_method == self::DOMAIN_VALIDATE_AS_PUBLIC && $this->domain_data_reader) {

                $this->setFromDomain(new Domain($domain, $this->domain_data_reader));

                // No special validation.
            } else {

                $this->domain = $domain;
            }

            $this->is_ip_address = false;

            // Should be an IP Address, because the domain is enclosed in square brackets.
        } else {

            // Removing enclosed brackets, because function "filter_var" will not recognize an IP address with square brackets.
            $domain_trimmed = substr($domain, 1, -1);

            // This will also validate the IP address.
            $ip_address = new IpAddress($domain_trimmed);

            // It should be valid from this point on.
            $this->setFromIpAddress($ip_address);
            $this->is_ip_address = true;
        }
    }


    // Gets domain part.

    public function getDomainPart(): string|Hostname|Domain|IpAddress
    {

        return $this->domain;
    }


    // Gets domain part comments.

    public function getDomainComments(): array
    {

        return $this->domain_comments;
    }


    // Sets domain from the Domain object.

    public function setFromDomain(Domain $domain): void
    {

        $this->domain = $domain;
        $this->is_ip_address = false;
    }


    // Sets domain from the IP Address object.

    public function setFromIpAddress(IpAddress $ip_address): void
    {

        $this->domain = $ip_address;
        $this->is_ip_address = true;
    }


    // Sets domain from the Hostname object.

    public function setFromHostname(Hostname $hostname): void
    {

        $this->domain = $hostname;
        $this->is_ip_address = false;
    }


    // Extracts encapsulated comments from a so called "part" string.

    public static function extractPartComments(string $part): array
    {

        $result = [
            'segments' => [$part],
            'comments' => [],
        ];

        if (strlen($part) > 1 && ($part[0] == '(' || substr($part, -1) == ')')) {

            $enclosed_chars_iterator = new EnclosedCharsIterator($part, [
                '(' => [')', true],
            ]);

            $segments = [];

            foreach ($enclosed_chars_iterator as $key => $segment) {
                $segments[] = $segment;
            }

            /* Will capture comments that are in the first and in the last segments.
            https://en.wikipedia.org/wiki/Email_address states that "comments are allowed with
            parentheses at either end of the local-part". RFC2822 writes that "comments and
            white space throughout addresses, dates, and message identifiers are all part of
            the obsolete syntax", possibly referring to comments and whitespaces within the
            local part and domain part, eg. <1234   @   local(blah)  .machine .example> */

            if (substr($segments[0], 0, 1) == '(' && substr($segments[0], -1) == ')') {
                $result['comments'][] = array_shift($segments);
            }

            $last_segment = $segments[(count($segments) - 1)];

            if (substr($last_segment, 0, 1) == '(' && substr($last_segment, -1) == ')') {
                $result['comments'][] = array_pop($segments);
            }

            $result['segments'] = $segments;
        }

        return $result;
    }


    // Gives information about a local part string.

    public static function parseLocalPart(string $local_part): array
    {

        $result = [
            'contents' => $local_part,
            'is_quoted' => false,
            'comments' => [],
        ];

        $len = strlen($local_part);

        if ($len > 1) {

            $first_char = $local_part[0];
            $last_char = substr($local_part, -1);

            if ($first_char == '"' && $last_char == '"') {

                $result['is_quoted'] = true;
                $result['contents'] = substr($local_part, 1, -1);

            } else {

                $part_data = self::extractPartComments($local_part);
                $result['comments'] = $part_data['comments'];
                $result['contents'] = implode($part_data['segments']);
            }
        }

        return $result;
    }


    // Validates local-part string. Returns local part information array.

    public static function validateLocalPart(string $local_part): array
    {

        $len = strlen($local_part);

        if (!$len) {

            throw new InvalidEmailAddressLocalPartException(sprintf("Local-part \"%s\" must contain at least one character.", $local_part));

        } elseif ($len > 64) {

            throw new InvalidEmailAddressLocalPartException(sprintf("Local-part \"%s\" must not exceed 64 characters.", $local_part));

        } else {

            $local_part_data = self::parseLocalPart($local_part);
            $local_part = $local_part_data['contents'];
            $is_quoted = $local_part_data['is_quoted'];

            // Regex bit "\w" is equivalent of "[a-zA-Z0-9_]". Therefore the underscore ("_") is not in the special characters list.
            if (!$is_quoted && !preg_match('<^[\w\x{0080}-\x{FFFF}' . preg_quote("!#$%&'*+-/=?^`{|}~.") . ']+$>u', $local_part)) {

                throw new InvalidEmailAddressLocalPartException(sprintf("Unquoted local part \"%s\" must consist of unicode letters, numbers, and printable characters.", $local_part));

            } elseif (!$is_quoted && $local_part[0] == '.') {

                throw new InvalidEmailAddressLocalPartException(sprintf("Unquoted local part \"%s\" cannot start with a dot symbol (\".\").", $local_part));

            } elseif (!$is_quoted && substr($local_part, -1) == '.') {

                throw new InvalidEmailAddressLocalPartException(sprintf("Unquoted local-part \"%s\" cannot end with a dot symbol (\".\").", $local_part));

            } elseif (!$is_quoted && strpos($local_part, '..') !== false) {

                throw new InvalidEmailAddressLocalPartException(sprintf("Unquoted local part \"%s\" must not contain consecutive dot symbols (\"..\").", $local_part));

                // Regex bit "\w" is equivalent of "[a-zA-Z0-9_]". Therefore the underscore ("_") is not in the special characters list.
            } elseif ($is_quoted && !preg_match('<^[\w\x{0080}-\x{FFFF}\x00-\xFF\t\s' . preg_quote("!#$%&'*+-/=?^`{|}~.\"(),:;<>@[\]") . ']+$>u', $local_part)) {

                throw new InvalidEmailAddressLocalPartException(sprintf("Quoted local part \"%s\" must consist of unicode letters, numbers, and allowed special characters.", $local_part));

            } else {

                if ($is_quoted) {

                    /* Below is an attempt to make sure that a backslash or double-quote is preceded by a backslash. */

                    $is_escape = false;

                    for ($i = 0; $i < strlen($local_part); $i++) {

                        $char = $local_part[$i];

                        if (!$is_escape && $char == '\\') {

                            $is_escape = true;

                        } elseif ($is_escape && ($char == '\\' || $char == '"')) {

                            $is_escape = false;

                        } elseif ($is_escape) {

                            throw new InvalidEmailAddressLocalPartException(sprintf("A backslash at position %d should be succeeded by another backslash or a double quote.", $i));

                        } elseif (!$is_escape && ($char == '\\' || $char == '"')) {

                            throw new InvalidEmailAddressLocalPartException(sprintf("Character \"%s\" at position %d has not been escaped.", $char, $i));
                        }
                    }
                }
            }

            return $local_part_data;
        }
    }
}
