<?php

declare(strict_types=1);

namespace LWP\Network\Http\Auth;

use LWP\Common\String\Format;
use LWP\Network\Http\Auth\AuthAbstract;
use LWP\Network\Uri\UriReference;
use LWP\Network\Http\HttpMethodEnum;

class Digest extends AuthAbstract
{
    public const SCHEME_NAME = 'Digest';

    private string $username;
    private string $password;
    private array $params;
    private HttpMethodEnum $http_method;
    private UriReference $uri_reference;
    private string $nc;
    private string $cnonce;


    public function __construct(string $username, string $password, array $params, HttpMethodEnum $http_method, UriReference $uri_reference, int $counter = 1)
    {

        $this->username = $username;
        $this->password = $password;
        $this->params = $params;
        $this->http_method = $http_method;
        $this->uri_reference = $uri_reference;

        // Pad a number with leading zeros up to 8 numbers.
        $this->nc = sprintf('%08d', $counter);
        $this->cnonce = Format::nonce();
    }


    // Calculates hash 1.

    public function calculateHA1(): string
    {

        return md5(
            $this->username
            . ':'
            . $this->params['realm']
            . ':'
            . $this->password
        );
    }


    // Calculates hash 2.

    public function calculateHA2(): string
    {

        return md5(
            $this->http_method->name
            . ':'
            . $this->uri_reference->getPathString()
        );
    }


    // Calculates full response.

    public function getResponse(): string
    {

        return md5(
            $this->calculateHA1()
            . ':'
            . $this->params['nonce']
            . ':'
            . $this->nc
            . ':'
            . $this->cnonce
            . ':'
            . $this->params['qop']
            . ':'
            . $this->calculateHA2()
        );
    }


    // Builds the signature.

    public function buildSignature(): string
    {

        $result = ('username="' . $this->username . '"');

        foreach ($this->params as $key => $val) {

            $key = strtolower($key);

            if ($key === 'stale' || $key === 'username' || $key === 'password') {
                continue;
            }

            $result .= (', ' . $key . '="' . $val . '"');
        }

        $result .= (', uri="' . $this->uri_reference->getPathString() . '"');
        $result .= (', nc="' . $this->nc . '"');
        $result .= (', cnonce="' . $this->cnonce . '"');
        $result .= (', response="' . $this->getResponse() . '"');

        return $result;
    }
}
