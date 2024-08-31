<?php

declare(strict_types=1);

namespace LWP\Network\Http\Auth;

use LWP\Common\Common;
use LWP\Common\Exceptions\ExpiredException;
use LWP\Network\Http\Auth\Enums\JwtAlgorithmEnum;
use LWP\Network\Uri\Url;
use LWP\Network\Http\Auth\Exceptions\InvalidJwtTokenException;
use LWP\Network\Http\Auth\Exceptions\InvalidJwtTokenSignature;

class JwtParser
{
    public readonly string $header_str;
    public readonly string $payload_str;
    public readonly string $signature_str;
    public readonly array $header;
    public readonly array $payload;
    public readonly Jwt $jwt;


    public function __construct(
        public readonly string $signed_token,
        #[\SensitiveParameter]
        protected string $secret_key
    ) {

        [
            'header' => $this->header_str,
            'payload' => $this->payload_str,
            'signature' => $this->signature_str,
        ] = self::toParts($signed_token);

        $header = self::unpackHeader($this->header_str);

        self::validateHeader($header);
        $this->header = $header;

        $this->payload = self::unpackPayload($this->payload_str);
        $this->jwt = new Jwt($this->getAlgorithm(), $this->payload, $secret_key);

        [, $signed_token] = $this->jwt->getSignedTokenData();

        if ($signed_token !== $this->signature_str) {
            throw new InvalidJwtTokenSignature("JWT token signature is invalid");
        }

        if (isset($this->payload['exp']) && $this->payload['exp'] <= time()) {
            throw new ExpiredException("JWT token has expired");
        }
    }


    //

    public function getAlgorithm(): JwtAlgorithmEnum
    {

        return Common::findEnumCase(JwtAlgorithmEnum::class, $this->header['alg']);
    }


    //

    public static function validateHeader(array $header): true
    {

        if (isset($header['typ'])) {

            if ($header['typ'] !== Jwt::TYPE_STRING) {
                throw new InvalidJwtTokenException(sprintf(
                    "Header's element \"typ\" must be set to %s",
                    Jwt::TYPE_STRING
                ));
            }

            unset($header['typ']);
        }

        if (!isset($header['alg'])) {
            throw new InvalidJwtTokenException("Header's element \"alg\" is missing");
        }

        $find_algorithm = Common::findEnumCase(JwtAlgorithmEnum::class, $header['alg']);

        if (!$find_algorithm) {
            throw new InvalidJwtTokenException(sprintf(
                "Unrecognized algorithm %s",
                $header['alg']
            ));
        }

        return true;
    }


    //

    public static function unpackHeader(string $header_str): array
    {

        try {
            $header = json_decode(
                Url::base64UrlDecode($header_str),
                associative: true,
                flags: JSON_THROW_ON_ERROR
            );
        } catch (\JsonException) {
            throw new InvalidJwtTokenException("Header string could not be decoded");
        }

        return $header;
    }


    //

    public static function unpackPayload(string $payload_str): array
    {

        try {
            $payload = json_decode(
                Url::base64UrlDecode($payload_str),
                associative: true,
                flags: JSON_THROW_ON_ERROR
            );
        } catch (\JsonException) {
            throw new InvalidJwtTokenException("Payload string could not be decoded");
        }

        return $payload;
    }


    //

    public static function toParts(string $signed_token): array
    {

        $parts = explode(Jwt::TOKEN_PART_SEPARATOR, $signed_token, 3);

        if (count($parts) !== 3) {
            throw new InvalidJwtTokenException("JWP token must contain of 3 parts separated by 2 dots");
        }

        return [
            'header' => $parts[0],
            'payload' => $parts[1],
            'signature' => $parts[2]
        ];
    }
}
