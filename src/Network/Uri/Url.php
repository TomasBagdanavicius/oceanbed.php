<?php

declare(strict_types=1);

namespace LWP\Network\Uri;

use LWP\Network\Domain\DomainDataReader;
use LWP\Network\Request as NetworkRequest;
use LWP\Network\Uri\Exceptions\InvalidUrlException;

class Url extends UrlReference implements UriInterface
{
    use UriTrait;


    public const DEFAULT_PROTOCOL = 'http';


    public function __construct(
        string $url_str,
        int $host_validate_method = self::HOST_VALIDATE_REGULAR,
        DomainDataReader $domain_data_reader = null,
        array $known_hosts = []
    ) {

        parent::__construct($url_str, $host_validate_method, $domain_data_reader, $known_hosts);

        if (!$this->getScheme()) {
            throw new InvalidUrlException(sprintf(
                "URL \"%s\" does not contain a scheme. Must have one of the following: %s.",
                $url_str,
                implode(', ', parent::getAcceptedSchemes())
            ));
        } elseif (!$this->hasAuthority()) {
            throw new InvalidUrlException("URL \"$url_str\" does not contain an authority part.");
        } elseif (!$this->getHost()) {
            throw new InvalidUrlException("URL \"$url_str\" does not contain a host name part.");
        }
    }


    // Builds a remote socket URI from the components of this URL.

    public function getAsRemoteSocketUri(): UriReference
    {

        return NetworkRequest::buildRemoteSocketUri($this->getScheme(), $this->getHost(), $this->getDefaultPortNumber());
    }


    // Creates a new reference object.

    public function getNewReferenceInstance(string $url_str): UrlReference
    {

        return new UrlReference($url_str);
    }

    /**
     * Base64 URL encodes a string.
     *
     * @param string $string The input string to be encoded.
     * @return string The base64 URL encoded string.
     */
    public static function base64UrlEncode(string $string): string
    {

        return rtrim(strtr(base64_encode($string), '+/', '-_'), '=');
    }

    /**
     * Base64 URL decodes a base64 URL encoded string.
     *
     * @param string $base64_url_str The base64 URL encoded string to be decoded.
     * @return string The decoded original string.
     */
    public static function base64UrlDecode(string $base64_url_str): string
    {

        return base64_decode(strtr($base64_url_str, '-_', '+/'));
    }
}
