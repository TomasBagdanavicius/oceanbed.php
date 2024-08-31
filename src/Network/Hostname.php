<?php

declare(strict_types=1);

namespace LWP\Network;

class Hostname implements \Stringable
{
    public const LABEL_SEPARATOR = '.';

    private $labels = [];


    public function __construct(string $hostname)
    {

        self::validateHostname($hostname);

        $this->labels = self::splitIntoLabels($hostname);
    }


    // Joints labels into a string.

    public function __toString(): string
    {

        return self::joinLabels($this->labels);
    }


    // Gets labels.

    public function getLabels(): array
    {

        return $this->labels;
    }


    // Validates a host name.
    /* This is a kick-start function to validate a host name and provide more info about failure reasons, because "filter_var" apparently returns only a "false" on failure. */

    public static function validateHostname(string $hostname): void
    {

        // Cannot end with 2 or more label separator symbols.
        if (str_ends_with($hostname, str_repeat(self::LABEL_SEPARATOR, 2))) {
            throw new Exceptions\InvalidHostnameException(sprintf("Host name \"%s\" cannot end with 2 or more label separator symbols (\"%s\").", $hostname, self::LABEL_SEPARATOR));
        }

        if (!filter_var($hostname, FILTER_VALIDATE_DOMAIN, FILTER_FLAG_HOSTNAME)) {
            throw new Exceptions\InvalidHostnameException(sprintf("Invalid hostname \"%s\".", $hostname));
        }
    }


    // Validates a single label.
    // Variable $accept_underscore was adopted mainly to accept underscores that are valid in domain names.

    public static function validateLabel(string $label, bool $accept_underscore = false): bool
    {

        $allowed_extra_chars = ['-'];

        if ($accept_underscore) {
            $allowed_extra_chars[] = '_';
        }

        $len = strlen($label);

        if (!$len) {
            throw new \Exception("Hostname label \"$label\" must contain at least one character.");
        } elseif ($len > 63) {
            throw new \Exception("Hostname label \"$label\" must not exceed 63 characters.");
        } elseif (!ctype_alnum(str_replace($allowed_extra_chars, '', $label))) {
            throw new \Exception("Hostname label \"$label\" must consist of alphanumerics or hyphens.");
        } elseif ($label[0] === '-') {
            throw new \Exception("Hostname label \"$label\" must not start with a dash character.");
        } elseif (substr($label, -1) == '-') {
            throw new \Exception("Hostname label \"$label\" must not end with a dash character.");
        }

        return true;
    }


    // Joins an array of labels into a domain name string by using the labels separator.

    public static function joinLabels(array $labels): string
    {

        return implode(self::LABEL_SEPARATOR, $labels);
    }


    // Splits a domain name into labels.

    public static function splitIntoLabels(string $hostname): array
    {

        return explode(self::LABEL_SEPARATOR, $hostname);
    }
}
