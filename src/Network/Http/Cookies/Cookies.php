<?php

/* Notes
 *
 * Convention: parameters in cookie header string will be called "attributes", whereas in PHP code - "options".
 * Cookie param/directive names are case-insensitive.
 * It appears that cookies are not bound to a port.
 *
 */

/* To Do
 *
 * Add criteria and validation management for the definitions below. Currently some of the things are validated, whereas others are not.
 *
 */

namespace LWP\Network\Http\Cookies;

use LWP\DateTime\DateTime;
use LWP\Network\Http\Server;
use LWP\Network\Headers;
use LWP\Common\String\Format;
use LWP\Network\Http\Cookies\Exceptions\UnrecognizedAttribute;
use LWP\Components\Validators\DateTimeValidator;
use LWP\Components\DataTypes\Custom\DateTime\Exceptions\InvalidDateTimeException;

class Cookies
{
    public const COOKIE_HEADER_FIELD_NAME = 'Cookie';
    public const SET_COOKIE_HEADER_FIELD_NAME = 'Set-Cookie';

    public const SECURE_PREFIX = '__Secure-';
    public const HOST_PREFIX = '__Host-';

    public const MAX_SIZE = 4095;
    public const DIRECTIVE_SEPARATOR = ';';
    public const PAIR_SEPARATOR = '=';

    public const STRICT_PARSE = 1;


    // This is a draft of definitions.
    public static $definitions = [
        // To do: max sum of name and value str lengths should not exceed MAX_SIZE.
        'name' => [
            'type' => 'string',
            'required_groups' => [
                1, // name-or-value
            ],
        ],
        'value' => [
            'type' => 'string',
            'required_groups' => [
                1, // name-or-value
            ],
        ],
        'expires' => [
            'type' => 'datetime',
            'format' => 'D, d-M-Y H:i:s \G\M\T',
            'allow_empty' => 1,
            'title_case' => 'Expires',
        ],
        'max-age' => [
            'type' => 'integer',
            'minlength' => 1,
            'maxlength' => 9,
            'allow_empty' => 1,
            'title_case' => 'Max-Age',
        ],
        'domain' => [
            'type' => 'domain',
            'allow_empty' => 1,
            'title_case' => 'Domain',
        ],
        'path' => [
            'type' => 'path',
            'allow_empty' => 1,
            'title_case' => 'Path',
        ],
        'secure' => [
            'type' => 'boolean',
            'allow_empty' => 1,
            'title_case' => 'Secure',
        ],
        // When true the cookie will be made accessible only through the HTTP protocol.
        // This means that the cookie won't be accessible by scripting languages, such as JavaScript.
        'httponly' => [
            'type' => 'boolean',
            'allow_empty' => 1,
            'title_case' => 'HttpOnly',
        ],
        'samesite' => [
            'type' => 'string',
            'values' => [
                'None',
                'Strict',
                'Lax',
            ],
            'default_value' => 'Lax',
            'title_case' => 'SameSite',
        ],
    ];


    // Gets a list of all valid attributes.

    public static function getAttributesList(): array
    {

        return [
            'domain',
            'path',
            'expires',
            'max-age',
            'httponly',
            'secure',
            'samesite',
        ];
    }


    // Tells if a given attribute exists.

    public static function attributeExists(string $name): bool
    {

        return in_array(strtolower($name), self::getAttributesList());
    }


    // Joins name and value tokens into a pair string.

    public static function joinPair(string $name, string $value): string
    {

        return ($name . self::PAIR_SEPARATOR . $value);
    }


    // Gets default option values.

    public static function getDefaultOptionValues(): array
    {

        $result = [];

        foreach (self::$definitions as $attribute_name => $data) {

            if (isset($data['default_value']) || array_key_exists('default_value', $data)) {
                $result[$attribute_name] = $data['default_value'];
            }
        }

        return $result;
    }


    // Parses "Set-Cookie" header field and returns info about its components.

    public static function parseSetCookieHeaderField(string $header_field, int $flags = null): array
    {

        $header_parts = Headers::parseField($header_field);

        $header_parts['value'] = self::parseSetCookieHeaderFieldValue($header_parts['value'], $flags);

        return $header_parts;
    }


    // Parses "Cookie" header field and returns info about its components.

    public static function parseCookieHeaderField(string $header_field): array
    {

        $header_parts = Headers::parseField($header_field);

        $header_parts['value'] = self::parseCookieHeaderFieldValue($header_parts['value']);

        return $header_parts;
    }


    // Parses "Set-Cookie" header field's value.

    public static function parseSetCookieHeaderFieldValue(string $field_value, int $flags = null): array
    {

        $strict_mode = ($flags & self::STRICT_PARSE);

        $result = [];

        // Find cookie name and value first, because cookie name might be ambigous with other directive names.
        // Semicolon is a control character, prohibited in cookie name, value and other parts.
        $split = explode(self::DIRECTIVE_SEPARATOR, $field_value, 2);
        $name_value = explode(self::PAIR_SEPARATOR, $split[0], 2);

        // When "=" symbol is absent in the name-value pair, browsers treat it as cookie with empty-string name.
        if (count($name_value) === 1) {

            // Prepend empty value to the beginning of array.
            array_unshift($name_value, '');
        }

        if ($name_value[0] !== '') {

            // Check if "secure" prefix is used.
            if (str_starts_with($name_value[0], self::SECURE_PREFIX)) {

                $result['secure_prefix'] = true;
                // Browsers will not strip it off, but here it's best to have to separate params.
                $name_value[0] = substr($name_value[0], strlen(self::SECURE_PREFIX));

                // Check if "host" prefix is used.
            } elseif (str_starts_with($name_value[0], self::HOST_PREFIX)) {

                $result['host_prefix'] = true;
                // Browsers will not strip it off, but here it's best to have to separate params.
                $name_value[0] = substr($name_value[0], strlen(self::HOST_PREFIX));
            }
        }

        $result['name'] = $name_value[0];
        // Leading and trailing quotes should not be trimmed. They are part of the value.
        $result['value'] = $name_value[1];

        if (isset($split[1])) {

            $directives = trim($split[1]);

            if ($directives !== '') {

                // Explode the remainder of the string.
                $parts = explode(self::DIRECTIVE_SEPARATOR, $directives);

                if ($parts) {

                    foreach ($parts as $part) {

                        $part = trim($part);
                        $data = explode(self::PAIR_SEPARATOR, $part, 2);
                        $name = strtolower($data[0]);

                        if (isset(self::$definitions[$name])) {

                            $result[$name] = (isset($data[1]))
                                ? Format::trimMatchingQuotes($data[1])
                                : ((self::$definitions[$name]['type'] === 'boolean')
                                    ? true
                                    : '');

                        } elseif ($strict_mode) {

                            throw new UnrecognizedAttribute("Unrecognized HTTP cookie attribute \"" . $data[0] . "\".");
                        }
                    }
                }
            }
        }

        return $result;
    }


    // Parses "Cookie" header field's value.

    public static function parseCookieHeaderFieldValue(string $field_value): array
    {

        // Does not take into consideration quoted semicolons. This can be achieved with \LWP\Common\String\EnclosedCharsIterator.
        $pairs = explode(self::DIRECTIVE_SEPARATOR, $field_value);
        // The result will be a 2-dimensional array, because both - cookie name and value - can come back empty.
        $result = [];

        foreach ($pairs as $pair) {

            $pair = trim($pair);

            // Ignores empty segments.
            if ($pair !== '') {

                $parts = explode(self::PAIR_SEPARATOR, $pair, 2);

                if (count($parts) > 1) {

                    $result[] = [
                        'name' => $parts[0],
                        'value' => $parts[1],
                    ];

                    // When "=" separator symbol is absent, the policy is to treat the part string as cookie value.
                } else {

                    $result[] = [
                        'name' => '',
                        'value' => $parts[0],
                    ];
                }
            }
        }

        return $result;
    }


    // Merges in default options.

    public static function addDefaultOptions(array &$data): void
    {

        $data = array_merge(self::getDefaultOptionValues(), $data);
    }


    // Gets default current URI component options.

    public static function getDefaultURLComponentOptions(): array
    {

        return [
            'domain' => Server::getHost(),
            'path' => Server::getUrlPath(),
        ];
    }


    // Merges in the relevant components from the current URI.

    public static function addCurrentURIComponentOptions(array &$data): void
    {

        $data = array_merge(self::getDefaultURLComponentOptions(), $data);
    }


    // Sets a single HTTP cookie.
    /* Note! Once cookie is sent, it won't be immediatelly available in "$_COOKIE". One cannot check if cookie was successfully sent by examining "$_COOKIE" in the same request. Class "CookieStorage" should be used to predict if cookie is accepted and to create a mirror storage. */

    public static function set(string $name, string $value, array $options = []): void
    {

        self::validateName($name);
        self::validateValue($value);

        // @return - void.
        // @var 2 - whether this header should replace a previous similar header.
        header(self::buildSetCookieHeaderField($name, $value, $options), false);
    }


    // Sets a cookie from a data payload.

    public function setFromData(array $data): void
    {

        if (!isset($data['name'])) {
            throw new \Exception("Element \"name\" is missing in data container.");
        }

        if (!isset($data['value'])) {
            throw new \Exception("Element \"value\" is missing in data container.");
        }

        $name = $data['name'];
        $value = $data['value'];

        unset($data['name'], $data['value']);

        self::send($name, $value, $data);
    }


    // Sets a simple HTTP cookie, which does not require any additional parameters and contains name and value only.

    public function setSimple(string $name, string $value, bool $encode = false, bool $replace = false): void
    {

        if ($encode) {

            $name = urlencode($name);
            $value = urlencode($value);
        }

        self::validateName($name);
        self::validateValue($value);

        header(self::SET_COOKIE_HEADER_FIELD_NAME . ': ' . self::joinPair($name, $value), $replace);
    }


    // Sets multiple HTTP cookies.

    public static function setMulti(array $cookies): void
    {

        foreach ($cookies as $data) {

            self::setFromData($data);
        }
    }


    // Sets HTTP cookies from a cookie header field value.

    public static function setFromHeaderFieldValue(string $field_value): void
    {

        self::setFromData(self::parseSetCookieHeaderFieldValue($field_value));
    }


    // Sets multiple cookies from multiple cookie header field values.

    public static function setFromHeaderFieldValueMulti(array $field_values): void
    {

        foreach ($field_values as $field_value) {

            self::setFromHeaderFieldValue($field_value);
        }
    }


    // Builds a "Set-Cookie" header field string.
    // @var bool $encode - whether to encode cookie name and value.

    public static function buildSetCookieHeaderField(string $name, string $value, array $options = [], bool $encode = false): string
    {

        if ($name === '' && $value === '') {
            throw new \Exception("Either name or value must contain a character in the string.");
        }

        if ($encode) {

            $name = urlencode($name);
            $value = urlencode($value);
        }

        self::validateName($name);
        self::validateValue($value);

        $options = array_change_key_case($options, CASE_LOWER);

        $result = (self::SET_COOKIE_HEADER_FIELD_NAME . ': ');

        if ($name !== '') {

            $result .= ($name . self::PAIR_SEPARATOR);
        }

        $result .= $value;

        foreach ($options as $key => $value) {

            if (isset(self::$definitions[$key])) {

                $definitions = self::$definitions[$key];

                if ($definitions['type'] !== 'boolean' || $value === true) {

                    if ($definitions ['type'] === 'datetime') {

                        $value = DateTime::isValidTimeStamp($value)
                            ? gmdate($definitions['format'], $value)
                            : $value;
                    }

                    $result .= ('; ' . $definitions ['title_case']);

                    if ($definitions ['type'] !== 'boolean') {

                        $result .= (self::PAIR_SEPARATOR . $value);
                    }
                }

            } else {

                throw new UnrecognizedAttribute("Unrecognized HTTP cookie attribute \"" . $key . "\".");
            }
        }

        return $result;
    }


    // Builds a "Cookie" header field string.

    public static function buildCookieHeaderField(array $dataset): string
    {

        $result = (self::COOKIE_HEADER_FIELD_NAME . ': ');

        $i = 0;

        foreach ($dataset as $data) {

            if ($i > 0) {
                $result .= '; ';
            }

            if (!isset($data['name'])) {
                throw new \Exception("Element \"name\" is missing in dataset item #" . $i . ".");
            }

            if (!isset($data['value'])) {
                throw new \Exception("Element \"value\" is missing in dataset item #" . $i . ".");
            }

            $result .= self::joinPair($data['name'], $data['value']);

            $i++;
        }

        return $result;
    }


    // Removes a single HTTP cookie. The definited 3 params form a unique entry.

    public static function remove(string $name, ?string $domain = null, ?string $path = null): void
    {

        $options = self::getDefaultURLComponentOptions();

        if ($domain) {
            $options['domain'] = $domain;
        }

        if ($path) {
            $options['path'] = $path;
        }

        $options['max-age'] = 0;

        self::set($name, '', $options);
    }


    // Removes a HTTP cookie by a data payload.

    public static function removeByAttrs(array $data): void
    {

        if (!isset($data['name'])) {
            throw new \Exception("Element \"name\" is missing in data container.");
        }

        self::remove($data['name'], ($data['domain'] ?? null), ($data['path'] ?? null));
    }


    // Removes multiple HTTP cookies.

    public static function removeMulti(array $cookie_dataset): void
    {

        foreach ($cookie_dataset as $data) {

            self::removeByAttrs($data);
        }
    }


    // Validates the "name" parameter.

    public static function validateName(string $name): bool
    {

        return self::validateToken($name);
    }


    // Validates the "value" parameter.

    public static function validateValue(string $value): bool
    {

        return self::validateToken($value);
    }


    // Validates a HTTP cookie token (used for "name" and "value" parameters).

    public static function validateToken(string $token): bool
    {

        if ($token !== '') {

            // Allow alphanums + whitespace + the following special characters.
            $special_chars = "!@#$%^&*()-_+={}[]:'\|`~,<>./?";

            if (strlen($token) > self::MAX_SIZE) {
                throw new \Exception("Cookie token \"" . $token . "\" length must not exceed " . self::MAX_SIZE . " characters.");
            } elseif (!preg_match(';^[a-zA-Z0-9\s' . preg_quote($special_chars) . ']+$;', $token)) {
                throw new \Exception("Cookie token \"" . $token . "\" contains unallowed characters. It must consist of aphanums, whitespace, and \"" . $special_chars . "\" only. Try filtering out unallowed characters or try encoding the token.");
            }
        }

        return true;
    }


    // Validates the "max-age" parameter.

    public static function validateMaxAge(int|string $max_age): bool
    {

        if (!is_numeric($max_age)) {
            throw new \Exception("Attribute's \"max-age\" value must be numeric.");
        }

        return true;
    }


    // Validates the "expires" parameter.

    public static function validateExpires(string $expires): bool
    {

        try {
            return (new DateTimeValidator($expires))->validate(self::$definitions['expires']['format']);
        } catch (InvalidDateTimeException $exception) {
            return false;
        }
    }
}
