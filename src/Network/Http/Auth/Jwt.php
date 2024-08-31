<?php

declare(strict_types=1);

namespace LWP\Network\Http\Auth;

use LWP\Network\Http\Auth\Enums\JwtAlgorithmEnum;
use LWP\Network\Uri\Url;

class Jwt extends Bearer
{
    public const TYPE_STRING = 'JWT';
    public const TOKEN_PART_SEPARATOR = '.';
    public const RESERVED_CLAIMS = [
        'iss', // Issuer
        'exp', // Expiration time
        'sub', // Subject
        'aud' // Audience
    ];


    public function __construct(
        public readonly JwtAlgorithmEnum $algorithm,
        public readonly array $payload,
        #[\SensitiveParameter]
        protected string $secret_key
    ) {

        parent::__construct($this->getToken());
    }


    //

    public function getHeader(): array
    {

        return [
            'alg' => $this->algorithm->name,
            'typ' => self::TYPE_STRING
        ];
    }


    //

    public function getHeaderString(): string
    {

        return Url::base64UrlEncode(json_encode($this->getHeader(), flags: JSON_THROW_ON_ERROR));
    }


    //

    public function getPayloadString(): string
    {

        return Url::base64UrlEncode(json_encode($this->payload, flags: JSON_THROW_ON_ERROR));
    }


    //

    public function getUnsignedToken(): string
    {

        return ($this->getHeaderString() . self::TOKEN_PART_SEPARATOR . $this->getPayloadString());
    }


    //

    public function getSignedTokenData(): array
    {

        $unsigned_token = $this->getUnsignedToken();

        return [
            $unsigned_token,
            $this->algorithm->encode($unsigned_token, $this->secret_key)
        ];
    }


    //

    public function getSignedToken(): string
    {

        [, $signed_token] = $this->getSignedTokenData();

        return $signed_token;
    }


    //

    public function getToken(): string
    {

        [$unsigned_token, $signed_token] = $this->getSignedTokenData();

        return ($unsigned_token . self::TOKEN_PART_SEPARATOR . $signed_token);
    }


    //

    public function extractReservedClaims(): array
    {

        return array_intersect_key($this->payload, array_flip(self::RESERVED_CLAIMS));
    }
}
