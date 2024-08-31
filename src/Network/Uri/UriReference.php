<?php

declare(strict_types=1);

namespace LWP\Network\Uri;

use LWP\Network\Uri\Exceptions\InvalidUriSchemeException;

class UriReference implements UriInterface, \Stringable
{
    public const AUTHORITY_PREFIX = '//';
    public const QUERY_COMPONENT_PREFIX = '?';
    public const FRAGMENT_COMPONENT_PREFIX = '#';

    protected array $parts = [
        'scheme' => '',
        'scheme_divider' => '',
        'has_authority' => '',
        'authority' => '',
        // Userinfo is not split into username and password, because they need to support "false" values.
        'userinfo' => '',
        'userinfo_divider' => '',
        'host' => '',
        'port' => '',
        'path_query_fragment' => '',
        'path' => '',
        'query' => '',
        'fragment' => '',
    ];
    private array $scheme_classes = [
        'mailto' => 'UriMailto',
        'data' => 'UriData',
        'http' => 'Url',
        'https' => 'Url',
    ];


    public function __construct(
        string $uri,
        protected bool $strict_mode = false,
    ) {

        // Contains scheme.
        if ($parts = self::parseScheme($uri)) {

            list($scheme, $uri) = $parts;
            $this->setScheme($scheme);
        }

        // Non-strict mode (false for the 2nd argument) to allow empty authorities, as in file URIs, eg. "file:///c:/WINDOWS/clock.avi".
        if (self::isSchemeRelative($uri, strict_mode: false)) {

            $this->parts['has_authority'] = self::AUTHORITY_PREFIX;
            $uri = substr($uri, 2);
            $authority_end_pos = self::findAuthorityEndPos($uri);

            if ($authority_end_pos !== false) {

                $this->parts['authority'] = substr($uri, 0, $authority_end_pos);
                $this->parts['path_query_fragment'] = substr($uri, $authority_end_pos);

            } else {

                $this->parts['authority'] = $uri;
            }

            $uri = null;
        }

        if (!empty($uri)) {

            $this->parts['path_query_fragment'] = $uri;
        }
    }


    // Returns the whole URI string.

    public function __toString(): string
    {

        return $this->getUri();
    }


    // On object clone

    public function __clone(): void
    {

        $components = [
            'query',
            'path'
        ];

        foreach ($components as $component) {
            if (is_object($this->parts[$component])) {
                $this->parts[$component] = clone $this->parts[$component];
            }
        }
    }


    // Gets URI parts.

    public function getParts(): array
    {

        return $this->parts;
    }


    // Checks if class exists for the current scheme.

    public function hasSchemeClass(): bool
    {

        $scheme = $this->getScheme();

        return (!empty($scheme) && isset($this->scheme_classes[$scheme]));
    }


    // Gets class name (not fully qualified) representing current scheme.

    public function getSchemeClassName(): ?string
    {

        if (!$this->hasSchemeClass()) {
            return null;
        }

        return $this->scheme_classes[$this->getScheme()];
    }


    // Gets scheme class instance.

    public function getSchemeClassInstance(): ?self
    {

        if (!$class_name = $this->getSchemeClassName()) {
            return null;
        }

        return new (__NAMESPACE__ . '\\' . $class_name)($this->__toString());
    }


    // Sets scheme.

    public function setScheme(string $scheme): string|false
    {

        if ($this->strict_mode && !self::validateScheme($scheme)) {
            return false;
        }

        $scheme = strtolower($scheme);

        $this->parts['scheme'] = $scheme;
        $this->parts['scheme_divider'] = ':';

        return $scheme;
    }


    // Gets scheme.

    public function getScheme(): string
    {

        return $this->parts['scheme'];
    }


    // Tells if URI is relative.

    public function isRelative(): bool
    {

        // Scheme is absent.
        return !$this->getScheme();
    }


    // Tells if URI is absolute.

    public function isAbsolute(): bool
    {

        return ($this->getScheme() && ($this->hasAuthority() || $this->getPathString()));
    }


    // Checks if URI contains authority component.

    public function hasAuthority(): bool
    {

        // check if the temporary container is not empty
        return (!empty($this->parts['has_authority']));
    }


    // Gets authority string.

    public function getAuthorityString(): string
    {

        return $this->getUriReference('authority', 'port');
    }


    // Sets authority string.

    public function setAuthorityString(string $authority_str): void
    {

        $this->parts['has_authority'] = self::AUTHORITY_PREFIX;
        $this->parts['authority'] = $authority_str;
        $this->parts['userinfo'] = $this->parts['userinfo_divider'] = $this->parts['host'] = $this->parts['port'] = '';
    }


    // Parse authority into userinfo, host, and port.

    public function splitAuthority(): void
    {

        // Check the temporary "authority" container.
        if (!empty($this->parts['authority'])) {

            $this->parts = array_merge(
                $this->parts,
                self::parseAuthority($this->parts['authority'])
            );

            // Flush the temporary container.
            $this->parts['authority'] = '';
        }
    }


    // Parses authority string statically.

    public static function parseAuthority(string $uri_authority): array
    {

        $result = [];

        // check if it contains an "@" sign
        // - the rule here is that a host name cannot contain an "@" sign, meaning that the last occurence is the division point
        // - if by mistake, this rule is incorrect, EnclosedCharsIterator can be used to track quoted and unquoted chars
        $at_sign_pos = strrpos($uri_authority, '@');

        if ($at_sign_pos !== false) {

            // userinfo is everything until the "at" sign
            $result['userinfo'] = substr($uri_authority, 0, $at_sign_pos);
            $result['userinfo_divider'] = '@';

            // this leaves the hostname and port number
            $uri_authority = substr($uri_authority, ($at_sign_pos + 1));
        }

        $colon_pos = strrpos($uri_authority, ':');

        if ($colon_pos !== false) {

            $str_after_colon = substr($uri_authority, ($colon_pos + 1));

            // if it's not fully numeric, it can be part of a IPv6 address
            if (is_numeric($str_after_colon)) {

                $result['port'] = (':' . $str_after_colon);
                $uri_authority = substr($uri_authority, 0, $colon_pos);
            }
        }

        $result['host'] = $uri_authority;

        return $result;
    }


    /* Host */

    // Gets host.
    // @param $real - whether to remove enclosing square brackets (usually used in IPv6 addresses).

    public function getHost(bool $real = true): string
    {

        $this->splitAuthority();

        $host_part = $this->parts['host'];

        return ($real && $host_part && $host_part[0] == '[' && substr($host_part, -1) == ']')
            // Removes enclosing square brackets.
            ? substr($host_part, 1, -1)
            : $host_part;
    }


    // Sets host.

    public function setHost(string $host): void
    {

        $this->splitAuthority();

        $this->parts['has_authority'] = self::AUTHORITY_PREFIX;
        $this->parts['host'] = $host;
    }


    /* Port Number */

    // Gets port number.

    public function getPortNumber(): string
    {

        $this->splitAuthority();

        // Pass through "0" ("zero") port numbers.
        if ($this->parts['port'] === '') {
            return $this->parts['port'];
        }

        return ltrim($this->parts['port'], ':');
    }


    // Sets port number.

    public function setPortNumber(int $port_number): void
    {

        $this->splitAuthority();

        $this->parts['port'] = (':' . $port_number);
    }


    // Tells if port number is set.

    public function hasPortNumber(): bool
    {

        $this->splitAuthority();

        return ($this->parts['port'] != '');
    }


    // Unsets port number.

    public function unsetPortNumber(): void
    {

        $this->splitAuthority();

        $this->parts['port'] = '';
    }


    /* Fragment */

    // Gets fragment.

    public function getFragment(bool $drop_prefix = false): string
    {

        if (empty($this->parts['fragment'])) {
            $this->splitPathQueryFragment();
        }

        $part_fragment = $this->parts['fragment'];

        return ($drop_prefix && $part_fragment && substr($part_fragment, 0, 1) === self::FRAGMENT_COMPONENT_PREFIX)
            ? substr($part_fragment, 1)
            : $part_fragment;
    }


    // Sets fragment.

    public function setFragment(string $fragment): void
    {

        $this->splitPathQueryFragment();

        $this->parts['fragment'] = (self::FRAGMENT_COMPONENT_PREFIX . $fragment);
    }


    // Removes fragment.

    public function removeFragment(): void
    {

        $this->splitPathQueryFragment();

        $this->parts['fragment'] = '';
    }


    /* User Info */

    // Parses user info data into username and password.

    public function parseAuthInfo(): array|false
    {

        $this->splitAuthority();

        if (empty($this->parts['userinfo'])) {
            return false;
        }

        return explode(':', $this->parts['userinfo']);
    }


    /* Username */

    // Gets username.
    // @return - either a username string (an empty string is a valid username) or "false".

    public function getUsername(): string|false
    {

        return ($auth_info = $this->parseAuthInfo())
            ? $auth_info[0]
            : false;
    }


    // Sets username.

    public function setUsername(string $username): void
    {

        $password = $this->getPassword();
        $this->parts['has_authority'] = self::AUTHORITY_PREFIX;
        $this->parts['userinfo'] = $username;
        $this->parts['userinfo_divider'] = '@';

        if ($password || $password == '') {
            $this->parts['userinfo'] .= (':' . $password);
        }
    }


    /* Password */

    // Gets password.
    // @return - either a password string (an empty string is a valid password) or "false".

    public function getPassword(): string|false
    {

        $auth_info = $this->parseAuthInfo();

        // pass through empty string passwords
        return ($auth_info && isset($auth_info[1]))
            ? $auth_info[1]
            : false;
    }


    // Sets password.

    public function setPassword(string $password): void
    {

        $username = $this->getUsername();

        if (empty($username)) {
            $username = '';
        }

        $this->parts['has_authority'] = self::AUTHORITY_PREFIX;
        $this->parts['userinfo'] = ($username . ':' . $password);
        $this->parts['userinfo_divider'] = '@';
    }


    // Parses path, query, and fragment parts.

    public function splitPathQueryFragment(): void
    {

        $path_component_string = '';

        // Check if the temporary container is not empty.
        if (!empty($this->parts['path_query_fragment'])) {

            // Look for the fragment first.
            $parts = explode(self::FRAGMENT_COMPONENT_PREFIX, $this->parts['path_query_fragment'], 2);

            // Fragment found.
            if (count($parts) > 1) {
                $this->parts['fragment'] = (self::FRAGMENT_COMPONENT_PREFIX . $parts[1]);
            }

            // Now look for the query string.
            $parts = explode(self::QUERY_COMPONENT_PREFIX, $parts[0], 2);

            // Query exists.
            if (count($parts) > 1) {
                $this->parts['query'] = (self::QUERY_COMPONENT_PREFIX . $parts[1]);
            }

            // We are left with the path.
            if (!empty($parts[0])) {
                $path_component_string = $parts[0];
            }

            // Flush temporary container.
            $this->parts['path_query_fragment'] = '';
        }

        // Ff path string is absent, feed an empty string into the 'UriPathComponent' class, because.
        // It should always be available to add new members to the path string.
        if ($this->parts['path'] === '') {
            $this->parts['path'] = new UriPathComponent($path_component_string, $this->strict_mode);
        }
    }


    /* Query */

    // Gets the query string.

    public function getQueryString(bool $drop_prefix = false): string
    {

        $this->splitPathQueryFragment();

        $part_query = $this->parts['query'];

        return ($drop_prefix && $part_query && substr($part_query, 0, 1) == self::QUERY_COMPONENT_PREFIX)
            ? substr($part_query, 1)
            : $part_query;
    }


    // Sets a given query string.

    public function setQueryString(string $query_string): void
    {

        if ($query_string && substr($query_string, 0, 1) !== self::QUERY_COMPONENT_PREFIX) {
            $query_string = (self::QUERY_COMPONENT_PREFIX . $query_string);
        }

        $this->splitPathQueryFragment();

        $this->parts['query'] = $query_string;
    }


    // Tells if the query string is not empty.

    public function hasQueryString(): bool
    {

        $this->splitPathQueryFragment();

        return ($this->parts['query'] !== '');
    }


    // Appends a given string to the query.

    public function appendToQueryString(string $query_string): void
    {

        $this->parts['query'] .= $query_string;
    }


    // Unsets query string.

    public function unsetQueryString(): void
    {

        $this->splitPathQueryFragment();

        $this->parts['query'] = '';
    }


    /* Path */

    // Gets the path component object.
    // Return type: Varies in other scheme instances.

    public function getPathComponent(): UriPathComponent
    {

        $this->splitPathQueryFragment();

        return $this->parts['path'];
    }


    // Creates path component from a string.

    public function setPathString(string $path_string): void
    {

        $this->splitPathQueryFragment();

        $this->parts['path'] = new UriPathComponent($path_string);
    }


    // Sets the path component object.

    public function setPathComponent(UriPathComponent $path_component): void
    {

        $this->splitPathQueryFragment();

        $this->parts['path'] = $path_component;
    }


    // Gets the path string.

    public function getPathString(): string
    {

        return $this->getPathComponent()->__toString();
    }


    /* Build */

    // Gets the whole URI string.

    public function getUri(): string
    {

        return $this->getUriReference();
    }


    // Gets parts for the whole URI or a portion of it.

    public function getUriReferenceParts(string $from = null, string $until = null): array
    {

        $parts_size = count($this->parts);

        // sets the offsets into a position to get the entire URI
        if (empty($from) && empty($until)) {

            $from_index = 0;
            $until_index = ($parts_size - 1);

        } else {

            $parts_keys = array_keys($this->parts);

            $from_index = (!empty($from))
                ? array_search($from, $parts_keys)
                : 0;

            $until_index = (!empty($until))
                ? array_search($until, $parts_keys)
                : ($parts_size - 1);

            if ($from_index > $until_index) {
                $until_index = $parts_size;
            }

            $authority_key_search = array_search('authority', $parts_keys);

            // check if any of the authority parts need to be in the result URI
            // +4 adds the 4 separate authority parts - userinfo, userinfo_divider, host, and port
            if ($from_index <= ($authority_key_search + 4) && $until_index >= $authority_key_search) {
                $this->splitAuthority();
            }

            $path_key_search = array_search('path_query_fragment', $parts_keys);

            // check if any of path, query, fragment parts need to be in the result URI
            // +4 adds the 4 separate authority parts - path, query_prefix, query_component, fragment
            if ($from_index <= ($path_key_search + 4) && $until_index >= $path_key_search) {
                $this->splitPathQueryFragment();
            }
        }

        $entire_uri = ($from_index == 0 && $until_index == ($parts_size - 1));

        $parts = $this->parts;

        if (!$entire_uri) {

            $parts = array_slice($parts, $from_index, ($until_index - $from_index + 1));
        }

        if (isset($parts['path']) && is_object($parts['path'])) {

            $parts['path'] = $parts['path']->__toString();
        }

        return $parts;
    }


    // Gets the whole URI or a portion of it.

    public function getUriReference(string $from = null, string $until = null): string
    {

        return $this->buildUriReference($this->getUriReferenceParts($from, $until));
    }


    // Builds URI reference string for an array of parts.

    protected function buildUriReference(array $parts): string
    {

        return implode($parts);
    }


    /* Static methods */

    // Checks if URI contains scheme.

    public static function parseScheme(string $uri): array|false
    {

        $parts = explode(':', $uri, 2);

        return (
            count($parts) === 2 // Colon exists.
            // If colon was preceeded by one of chars in the list, it means that non-scheme component started before the colon.
            && !preg_match('/[:\/?#]/', $parts[0])
        )
            ? $parts
            : false;
    }


    // Checks if URI is scheme relative.

    public static function isSchemeRelative(string $uri, bool $strict_mode = true): bool
    {

        return (
            substr($uri, 0, 2) === self::AUTHORITY_PREFIX // Starts with double forward slash.
            && (!$strict_mode || $uri[2] !== '/') // There is no 3rd forward slash in row (unless non-strict mode).
        );
    }


    // Checks if scheme string is valid.

    public static function validateScheme(string $scheme): bool
    {

        $first_char = $scheme[0];

        return (
            ctype_alpha($first_char) // First char must be alpha.
            && ctype_lower($first_char) // First char must also be lowercase.
            && !preg_match('/[^A-Za-z0-9+-\.]/', $scheme) // Scheme should only consist of alphas, digits, and '+', '-', '.' characters.
        );
    }


    // Find the position at which authority ends in an uri string that doesn't contain scheme, its divider or authority's leading slashes.

    public static function findAuthorityEndPos(string $uri_str): int|false
    {

        $result = false;
        // The order of these characters is important and should not be changed.
        $delimiting_chars = [UriPathComponent::SEPARATOR, self::QUERY_COMPONENT_PREFIX, self::FRAGMENT_COMPONENT_PREFIX];

        foreach ($delimiting_chars as $char) {

            $pos = strpos($uri_str, $char);

            if ($pos !== false) {
                $result = $pos;
                break;
            }
        }

        return $result;
    }


    // Checks if a static URI string is absolute.

    public static function staticIsAbsolute(string $uri): bool
    {

        $parts_by_colon = explode(':', $uri, 2);

        if (
            // Colon is non-existent.
            count($parts_by_colon) === 1
            // If colon was preceeded by one of chars below, it means that URI started before the colon.
            || preg_match('/[:\/?#]/', $parts_by_colon[0])
            || !self::validateScheme($parts_by_colon[0])
        ) {
            return false;
        } else {
            return true;
        }
    }


    // Checks if a static URI string is relative.

    public static function staticIsRelative(string $uri): bool
    {

        return !static::staticIsAbsolute($uri);
    }


    // Concatenates scheme and authority components into a string.

    public static function joinSchemeAndAuthority(string $scheme, string $authority): string
    {

        if (!self::validateScheme($scheme)) {
            throw new InvalidUriSchemeException("URI scheme \"{$scheme}\" is invalid.");
        }

        return ($scheme . ':' . self::AUTHORITY_PREFIX . $authority);
    }
}
