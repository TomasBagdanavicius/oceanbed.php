<?php

declare(strict_types=1);

namespace LWP\Network\Uri;

use LWP\Network\IpAddress;
use LWP\Network\Hostname;
use LWP\Network\Domain\Domain;
use LWP\Network\Domain\DomainDataReader;

class UrlReference extends UriReference implements UriInterface
{
    public const HOST_VALIDATE_NONE = 0; // Do not validate the host name at all.
    public const HOST_VALIDATE_REGULAR = 1; // Perform regular host name validation.
    public const HOST_VALIDATE_AS_DOMAIN_NAME = 2; // Validate host name as a domain name.

    private bool $is_ip;


    public function __construct(
        string $url,
        private int $host_validate_method = self::HOST_VALIDATE_REGULAR,
        private ?DomainDataReader $domain_data_reader = null,
        private array $known_hosts = [],
    ) {

        parent::__construct($url);

        $scheme = $this->getScheme();
        $path_query_fragment = $this->parts['path_query_fragment'];

        /* When all of the below conditions are met, consider that everything in the path-query-fragment part
        before the special "/", "?", "#" chars is the authority, eg. domain.com/foo/bar */
        if (
            $scheme == ''
            && !$this->hasAuthority()
            && !empty($path_query_fragment)
            && !in_array($path_query_fragment[0], [UriPathComponent::SEPARATOR, Uri::QUERY_COMPONENT_PREFIX, Uri::FRAGMENT_COMPONENT_PREFIX])
        ) {

            $this->parts['has_authority'] = UriReference::AUTHORITY_PREFIX;
            $authority_end_pos = parent::findAuthorityEndPos($path_query_fragment);

            if ($authority_end_pos !== false) {

                $this->parts['authority'] = substr($path_query_fragment, 0, $authority_end_pos);
                $this->parts['path_query_fragment'] = substr($path_query_fragment, $authority_end_pos);

            } else {

                $this->parts['authority'] = $path_query_fragment;
                $this->parts['path_query_fragment'] = '';
            }
        }

        $this->validateAgainstAcceptedSchemes($scheme);
        $this->splitAuthority();

        $this->domain_data_reader = $domain_data_reader;
        $this->host_validate_method = $host_validate_method;

        if ($this->parts['host'] != '') {
            $this->setHost($this->parts['host']);
        }

        $path_query_fragment = $this->parts['path_query_fragment'];

        /* Default the path to a single separator. The method below prepends a path separator staight to the 'path_query_fragment' part
        when the path is empty. Otherwise one would need to run "splitPathQueryFragment" method and add default path value over there. */
        if ($path_query_fragment == '' || in_array($path_query_fragment[0], [Uri::QUERY_COMPONENT_PREFIX, Uri::FRAGMENT_COMPONENT_PREFIX])) {
            $this->parts['path_query_fragment'] = (UriPathComponent::SEPARATOR . $this->parts['path_query_fragment']);
        }
    }


    // Tells if URL is relative.

    public function isRelative(): bool
    {

        return !$this->isAbsolute();
    }


    // Tells if URL is absolute.

    public function isAbsolute(): bool
    {

        return ($this->getScheme() && $this->hasAuthority());
    }


    // Sets the host name validation method.

    public function setHostValidateMethod(int $host_validate_method): void
    {

        $this->host_validate_method = $host_validate_method;
    }


    // Gets the current host name validation method.

    public function getHostValidateMethod(): int
    {

        return $this->host_validate_method;
    }


    // Gets domain data reader.

    public function getDomainDataReader(): ?DomainDataReader
    {

        return $this->domain_data_reader;
    }


    // Gets accepted schemes definitions.

    public static function getAcceptedSchemesDefinitions(): array
    {

        return [
            'https' => [
                'default_port' => 443,
            ],
            'http' => [
                'default_port' => 80,
            ],
            'ftp' => [
                'default_port' => 21,
            ],
        ];
    }


    // Gets an array of accepted schemes.

    public static function getAcceptedSchemes(): array
    {

        return array_keys(self::getAcceptedSchemesDefinitions());
    }


    // Validates a given scheme against the accepted schemes.

    public function validateAgainstAcceptedSchemes(string $scheme): void
    {

        $accepted_schemes = self::getAcceptedSchemes();

        if ($scheme != '' && !in_array($scheme, $accepted_schemes)) {
            throw new \Exception(sprintf(
                "Scheme \"$scheme\" is not an accepted URL scheme. Must be one of the following: %s.",
                implode(', ', $accepted_schemes)
            ));
        }
    }


    // Adds custom query handling behavior when parsing path, query, and fragment parts.

    public function splitPathQueryFragment(): void
    {

        parent::splitPathQueryFragment();

        if (!($this->parts['query'] instanceof SearchParams)) {

            $this->parts['query'] = SearchParams::fromString($this->parts['query']);
        }
    }


    // Gets the query string.

    public function getQueryString(bool $drop_prefix = false): string
    {

        $this->splitPathQueryFragment();

        return (!$drop_prefix)
            ? $this->parts['query']->outputWithPrefix()
            : $this->parts['query']->__toString();
    }


    // Tells if the query string is not empty.

    public function hasQueryString(): bool
    {

        $query_component = $this->getQueryComponent();

        return boolval($query_component->count());
    }


    // Gets the raw query component.

    public function getQueryComponent(): SearchParams
    {

        $this->splitPathQueryFragment();

        return $this->parts['query'];
    }


    // Sets a given query string.

    public function setQueryString(string $query_string): void
    {

        $this->splitPathQueryFragment();

        $this->parts['query'] = SearchParams::fromString($query_string);
    }


    // Sets a given query component object.

    public function setQueryComponent(SearchParams $query_component): void
    {

        $this->splitPathQueryFragment();

        $this->parts['query'] = $query_component;
    }


    // Unsets query string.

    public function unsetQueryString(): void
    {

        $this->splitPathQueryFragment();

        $this->parts['query']->clear();
    }


    // Gets parts for the whole URI or a portion of it.

    public function getUriReferenceParts(string $from = null, string $until = null): array
    {

        $parts = parent::getUriReferenceParts($from, $until);

        if (isset($parts['query']) && $parts['query'] instanceof SearchParams) {

            $parts['query'] = ($parts['query']->count())
                ? $parts['query']->outputWithPrefix()
                : '';
        }

        return $parts;
    }


    // Sets scheme.

    public function setScheme(string $scheme): string|false
    {

        $this->validateAgainstAcceptedSchemes($scheme);

        return parent::setScheme($scheme);
    }


    // Gets the host.

    public function getHost(bool $real = true): string
    {

        return (is_object($this->parts['host']))
            ? (string)$this->parts['host']
            : parent::getHost($real);
    }


    // Sets known hosts.

    public function addKnownHosts(array $known_hosts): array
    {

        $this->known_hosts = array_unique(array_merge($this->known_hosts, array_values($known_hosts)));
    }


    // Gets known hosts.

    public function getKnownHosts(): array
    {

        return $this->known_hosts;
    }


    // Checks if current host is a known host.

    public function isKnownHost(): bool
    {

        return in_array($this->getHost(), $this->known_hosts);
    }


    // Sets the host.

    public function setHost(string $host): void
    {

        $this->splitAuthority();

        $host_trimmed = $host;

        if ($host[0] === '[' && substr($host, -1) === ']') {

            // Removing enclosed brackets, because function "filter_var" will not recognize an IPv6 address with square brackets.
            $host_trimmed = substr($host, 1, -1);
        }

        // Not an IP address.
        // This will validate IPv4 and IPv6 addresses.
        if (!filter_var($host_trimmed, FILTER_VALIDATE_IP)) {

            $this->is_ip = false;

            if (in_array($host, $this->known_hosts)) {

                $this->parts['host'] = $host;

                // Validates as a hostname.
            } elseif ($this->host_validate_method === self::HOST_VALIDATE_REGULAR) {

                $this->parts['host'] = new Hostname($host);

                // Validates as a domain name.
            } elseif ($this->host_validate_method === self::HOST_VALIDATE_AS_DOMAIN_NAME && $this->domain_data_reader) {

                $this->setDomain(new Domain($host, $this->domain_data_reader));

                // No special validation.
            } else {

                $this->parts['host'] = $host;
            }

            // Host is a valid IP address.
        } else {

            $this->setIpAddress(new IpAddress($host));
            $this->is_ip = true;
        }
    }


    // Sets domain object into the host name slot.

    public function setDomain(Domain $domain): void
    {

        $this->parts['host'] = $domain;
        $this->is_ip = false;
    }


    // Get domain object instance, if available.

    public function getDomain(): ?Domain
    {

        return ($this->parts['host'] instanceof Domain)
            ? $this->parts['host']
            : null;
    }


    // Sets hostname object into the host name slot.

    public function setHostname(Hostname $hostname): void
    {

        $this->parts['host'] = $hostname;
        $this->is_ip = false;
    }


    // Get hostname object instance, if available.

    public function getHostname(): ?Hostname
    {

        return ($this->parts['host'] instanceof Hostname)
            ? $this->parts['host']
            : null;
    }


    // Sets IP address object into the host name slot.

    public function setIpAddress(IpAddress $ip_address): void
    {

        $this->parts['host'] = $ip_address;
        $this->is_ip = true;
    }


    // Get IP address object instance, if available.

    public function getIpAddress(): ?IpAddress
    {

        return ($this->parts['host'] instanceof IpAddress)
            ? $this->parts['host']
            : null;
    }


    // Gets the whole URL string. Alias of function "getUri".

    public function getUrl(): Url
    {

        $uri_str = $this->getUri();

        $uri_str = ($this->getScheme())
            ? $uri_str
            : (Url::DEFAULT_PROTOCOL . ':' . $uri_str);

        return new Url($uri_str, $this->host_validate_method, $this->domain_data_reader);
    }


    // Gets the whole URL or a portion of it.

    public function getUrlReference(string $from = null, string $until = null): string
    {

        return parent::getUriReference($from, $until);
    }


    // Gets punycode variant of the URL.

    public function getUrlPunycode(): string
    {

        $parts = $this->getUriReferenceParts();

        if ($parts['host'] instanceof Domain) {
            $parts['host'] = $parts['host']->getPunycode();
        }

        return $this->buildUriReference($parts);
    }


    // Unless port number was set, gets default port number.

    public function getStaticPortNumber(): ?int
    {

        return (($port_number = $this->getPortNumber()) !== '')
            ? $port_number
            : $this->getDefaultPortNumber();
    }


    // Gets default port number by scheme.
    // For non-HTTP protocols and services, see function "getservbyname".

    public function getDefaultPortNumber(): int
    {

        return self::getDefaultPortNumberByScheme($this->getScheme());
    }


    // Tells if the URL contains default port number.

    public function hasDefaultPortNumber(): bool
    {

        return (($port_number = $this->getPortNumber()) && $port_number == $this->getDefaultPortNumber());
    }


    // Gets default port number of an accepted scheme.
    // - This normally should be used when working within URL boundaries. When many schemes are involved, default "getservbyname" should be used.

    public static function getDefaultPortNumberByScheme(string $scheme): ?int
    {

        $scheme_definitions = self::getAcceptedSchemesDefinitions();

        return (isset($scheme_definitions[$scheme]))
            ? $scheme_definitions[$scheme]['default_port']
            : null;
    }


    // Statically checks if a URI string is absolute.

    public static function staticIsAbsolute(string $url): bool
    {

        if (!parent::staticIsAbsolute($url)) {
            return false;
        }

        $parts_by_colon = explode(':', $url, 2);

        if (!in_array($parts_by_colon[0], self::getAcceptedSchemes())) {
            return false;
        }

        return true;
    }
}
