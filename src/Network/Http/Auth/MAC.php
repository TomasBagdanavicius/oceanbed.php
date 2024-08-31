<?php

declare(strict_types=1);

namespace LWP\Network\Http\Auth;

use LWP\Common\String\Format;
use LWP\Network\Http\Auth\AuthAbstract;
use LWP\Network\Uri\Uri;
use LWP\Network\Uri\UrlReference;
use LWP\Network\Http\HttpMethodEnum;

class MAC extends AuthAbstract
{
    public const SCHEME_NAME = 'MAC'; // Stands for "Message Authentication Code".
    public const MAC_SIG_ALGORITHM_SHA1 = 'SHA1';
    public const MAC_SIG_ALGORITHM_SHA256 = 'SHA256';

    private Uri $uri;
    private HttpMethodEnum $http_method;
    private string $extension_string;
    private string $algorithm;
    private int $timestamp;
    private string $nonce;


    public function __construct(Uri $uri, HttpMethodEnum $http_method, string $extension_string, string $algorithm = self::MAC_SIG_ALGORITHM_SHA256)
    {

        $this->uri = $uri;
        $this->http_method = $http_method;
        $this->extension_string = $extension_string;
        $this->algorithm = $algorithm;

        $this->timestamp = time();
        $this->nonce = Format::nonce();
    }


    // Gets timestamp.

    public function getTimestamp(): int
    {

        return $this->timestamp;
    }


    // Gets nonce.

    public function getNonce(): string
    {

        return $this->nonce;
    }


    // Builds normalized request string.

    public function buildNormalizedRequestString(): string
    {

        $port = $this->uri->getPortNumber();

        if ($port == '') {
            $port = UrlReference::getDefaultPortNumberByScheme($this->uri->getScheme());
        }

        return (
            $this->timestamp . "\n"
            . $this->nonce . "\n"
            . $this->http_method->name . "\n"
            . $this->uri->getUriReference('path', 'query') . "\n"
            . $this->uri->getHost() . "\n"
            . (string)$port . "\n\n"
        );
    }


    // Builds signature.

    public function buildSignature(): string
    {

        $signature = base64_encode(
            hash_hmac(
                $this->algorithm,
                $this->buildNormalizedRequestString(),
                $this->extension_string,
                true
            )
        );

        return (
            'id="' . $this->extension_string . '",
            ts="' . $this->timestamp . '",
            nonce="' . $this->nonce . '",
            mac="' . $signature . '"'
        );
    }
}
