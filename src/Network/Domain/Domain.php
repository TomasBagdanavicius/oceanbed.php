<?php

declare(strict_types=1);

namespace LWP\Network\Domain;

use LWP\Common\String\Str;
use LWP\Network\Hostname;

class Domain implements \Stringable
{
    public const DEFAULT_HOSTNAME = 'www';

    private array $labels;
    private int $public_suffix_size;
    private string $domain_name;


    // This class doesn't extend LWP\Network\Hostname, because it's yet unclear, if each and every domain name is a hostname.
    public function __construct(
        string $domain_name,
        private DomainDataReader $data_reader
    ) {

        $domain_name = $this->domain_name = strtolower($domain_name);

        // Non-punycode domain name.
        if (strpos($domain_name, 'xn--') === false) {

            // First of, validate the structure, eg. length, segments composition.
            // Will validate full domain name length and labels, but will allow special characters.
            // Presumably, the maximum of 63 chars for a segment is when it contains simple characters only.
            $domain_name_ascii = (defined('INTL_IDNA_VARIANT_UTS46'))
                ? idn_to_ascii($domain_name, IDNA_NONTRANSITIONAL_TO_ASCII, INTL_IDNA_VARIANT_UTS46)
                : idn_to_ascii($domain_name);

            if (!$domain_name_ascii) {
                throw new Exceptions\InvalidDomainException("Domain name \"$domain_name\" is invalid.");
            }

            $domain_name_ascii_labels = self::splitIntoLabels($domain_name_ascii);

            // Additionally, check each label for unallowed special chars.
            foreach ($domain_name_ascii_labels as $label) {

                Hostname::validateLabel($label, true);
            }

            // Punnycode domain name.
        } elseif (

            // Will validate structure only. Will not validate punycode syntax.
            !self::validateDomainStructure($domain_name)
            // Will validate punycode syntax.
            || !idn_to_utf8($domain_name, IDNA_DEFAULT, INTL_IDNA_VARIANT_UTS46)

        ) {

            throw new Exceptions\InvalidDomainException("Domain name \"$domain_name\" is invalid.");
        }

        $this->labels = self::splitIntoLabels($domain_name);

        // Check whether this domain name corresponds with the public suffix list.
        if (!$public_suffix = $data_reader->getPublicSuffix($domain_name)) {
            throw new Exceptions\InvalidDomainException("Domain name \"$domain_name\" does not end with a valid public suffix.");
        }

        $this->public_suffix_size = self::getSize($public_suffix);
    }


    // Returns the full domain name.

    public function __toString(): string
    {

        return idn_to_utf8(Hostname::joinLabels($this->labels));
    }


    // Checks if it can be a fully qualified domain name.
    // A fully qualified domain name is a combination of hostname + registrable domain + root zone. This function assumes that a trailing period can be easily added, hence no checking.

    public function canBeFullyQualified(): bool
    {

        // Will check if there are further labels above the registrable part.
        return (count($this->labels) > ($this->public_suffix_size + 1));
    }


    // Returns fully qualified domain name.
    // A fully qualified domain name is a combination of hostname + registrable domain + root zone.

    public function getFullyQualified(): string|false
    {

        if ($this->canBeFullyQualified()) {
            return ($this->getPunycode() . Hostname::LABEL_SEPARATOR);
        } elseif ($this->getRegistrableDomain()) {
            return (self::DEFAULT_HOSTNAME . Hostname::LABEL_SEPARATOR . $this->getPunycode() . Hostname::LABEL_SEPARATOR);
        } else {
            return false;
        }
    }


    // Returns punycode variant of the full domain name.

    public function getPunycode(): string
    {

        return (defined('INTL_IDNA_VARIANT_UTS46'))
            ? idn_to_ascii($this->__toString(), IDNA_NONTRANSITIONAL_TO_ASCII, INTL_IDNA_VARIANT_UTS46)
            : idn_to_ascii($this->__toString());
    }


    // Gets labels.

    public function getLabels(): array
    {

        return $this->labels;
    }


    // Gets level number.

    public function getLevelNumber(): int
    {

        return count($this->labels);
    }


    // Prepends a new label to the domain name.

    public function prependLabel(string $label): void
    {

        Hostname::validateLabel($label, true);

        array_unshift($this->labels, $label);
    }


    // Prepends default hostname ("www") prefix label.

    public function prependDefaultHostname(): void
    {

        if ($this->labels[0] != self::DEFAULT_HOSTNAME) {

            $this->prependLabel(self::DEFAULT_HOSTNAME);
        }
    }


    // Gets top level domain.

    public function getTopLevelDomain(): string
    {

        return $this->labels[(count($this->labels) - 1)];
    }


    // Gets public suffix.

    public function getPublicSuffix(): string
    {

        return Hostname::joinLabels(array_slice($this->labels, -$this->public_suffix_size));
    }


    // Extracts the public suffix from a given domain name, and sets the result as the new public suffix.

    public function setPublicSuffix(string $domain_name): string
    {

        if (!$public_suffix = $this->data_reader->getPublicSuffix($domain_name)) {
            throw new \Exception("Invalid public suffix.");
        }

        array_splice($this->labels, -$this->public_suffix_size, $this->public_suffix_size, self::splitIntoLabels($public_suffix));

        $this->public_suffix_size = self::getSize($public_suffix);

        return $public_suffix;
    }


    // Gets registrable domain name.

    public function getRegistrableDomain(): ?string
    {

        return (count($this->labels) > $this->public_suffix_size)
            ? Hostname::joinLabels(array_slice($this->labels, -($this->public_suffix_size + 1)))
            : null;
    }


    // Extracts and validates the registrable domain from a given domain name, and sets it as the new registrable domain.

    public function setRegistrableDomain(string $domain_name): string
    {

        $parts = self::splitIntoLabels($domain_name);
        $parts_count = count($parts);

        if ($parts_count <= 1) {
            throw new \Exception(sprintf("Domain name must consist of at least two labels; %d given.", $parts_count));
        }

        if (!$public_suffix = $this->data_reader->getPublicSuffix($domain_name)) {
            throw new \Exception("Invalid public suffix.");
        }

        $public_suffix_size = self::getSize($public_suffix);

        if ($parts_count === $public_suffix_size) {
            throw new \Exception("There are no labels before the public suffix \"{$public_suffix}\".");
        }

        $main_label = implode(array_slice($parts, -($public_suffix_size + 1), 1));

        Hostname::validateLabel($main_label, true);

        // Replace the public suffix labels.
        array_splice($this->labels, -$this->public_suffix_size, $this->public_suffix_size, array_slice($parts, -$public_suffix_size));

        $this->public_suffix_size = $public_suffix_size;

        // Replace the one label before the public suffix.
        array_splice($this->labels, -($public_suffix_size + 1), 1, [$main_label]);

        return Hostname::joinLabels(array_slice($parts, -($public_suffix_size + 1)));
    }


    // List domain names at all levels.
    // $above_public_suffix - Whether public suffix domains should be excluded.
    // $exclude_default_hostname - Whether domain with the bottom-level default hostname ("www") should be excluded.

    public function walkThroughDomains(?\Closure $callback = null, bool $above_public_suffix = false, bool $exclude_default_hostname = false): \ArrayIterator
    {

        $labels = array_reverse($this->labels);
        $labels_count = count($labels);

        $domain_name = '';
        $result_data = [];

        if ($above_public_suffix) {

            $public_suffix = $this->getPublicSuffix();
            $public_suffix_reached = false;
        }

        foreach ($labels as $index => $label) {

            $domain_name = ($label . (($domain_name)
                ? (Hostname::LABEL_SEPARATOR . $domain_name)
                : ''));

            if ($above_public_suffix && !$public_suffix_reached) {

                if ($public_suffix === $domain_name) {
                    $public_suffix_reached = true;
                }

                continue;
            }

            $is_last_label = (($index + 1) === $labels_count); // Whether it is the last element.

            if ($exclude_default_hostname && $is_last_label && $label === self::DEFAULT_HOSTNAME) {
                continue;
            }

            $result_data[] = [$domain_name, $label];

            if ($callback) {

                $callback($domain_name, $label, $is_last_label);
            }
        }

        return new \ArrayIterator($result_data);
    }


    // Gets the number of qualified labels.

    public static function getSize(string $domain_name): int
    {

        return count(explode(Hostname::LABEL_SEPARATOR, trim($domain_name, Hostname::LABEL_SEPARATOR)));
    }


    // Splits a domain name into labels.

    public static function splitIntoLabels(string $domain_name): array
    {

        return Hostname::splitIntoLabels(trim($domain_name, Hostname::LABEL_SEPARATOR));
    }


    // Validates domain structure.
    // Function 'filter_var' will not work with internationalized domain names (IDNs).
    // It will validate special chars in punycode URLs.

    public static function validateDomainStructure(string $domain_name): bool
    {

        // Validate domain structure.
        return (filter_var($domain_name, FILTER_VALIDATE_DOMAIN) !== false);
    }


    // Removes default hostname ("www") prefix from the beginning of a domain name string.

    public static function removeDefaultHostname(string $domain_name): string
    {

        return Str::ltrimSubstring($domain_name, (self::DEFAULT_HOSTNAME . Hostname::LABEL_SEPARATOR));
    }


    // Adds default hostname ("www") prefix to the beginning of a domain name.

    public static function addDefaultHostname(string $domain_name): string
    {

        if (!str_starts_with($domain_name, self::DEFAULT_HOSTNAME . Hostname::LABEL_SEPARATOR)) {
            $domain_name = (self::DEFAULT_HOSTNAME . Hostname::LABEL_SEPARATOR . $domain_name);
        }

        return $domain_name;
    }


    // Checks if given domain name string contains default hostname ("www") prefix.

    public static function containsDefaultHostname(string $domain_name): bool
    {

        return str_starts_with($domain_name, (self::DEFAULT_HOSTNAME . Hostname::LABEL_SEPARATOR));
    }


    // Trims leading or trailing period in cases where a period is used to denote a domain suffix or where a trailing period binds a domain name to the root zone.
    /* In RTL language domain names the period appears to be at the end from PHP's string perspective. */

    public static function trimPeriod(string $domain_name): string
    {

        if (str_starts_with($domain_name, '.')) {
            $domain_name = substr($domain_name, 1);
        }

        if (str_ends_with($domain_name, '.')) {
            $domain_name = substr($domain_name, 0, -1);
        }

        return $domain_name;
    }
}
