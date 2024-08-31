<?php

declare(strict_types=1);

namespace LWP\Network\Http\Auth\Enums;

use LWP\Network\Uri\Url;

enum JwtAlgorithmEnum
{
    case HS256;
    case HS384;
    case HS512;
    case RS256;
    case RS384;
    case RS512;
    case ES256;
    case ES384;
    case ES512;


    // Encodes an unsigned token by a given enum case

    public function encode(string $unsigned_token, string $secret_key, ?string $cert = null): string
    {

        $hmac_encoder = function (string $algo) use ($unsigned_token, $secret_key): string {
            return hash_hmac($algo, $unsigned_token, $secret_key, binary: true);
        };

        $openssl_encoder = function (int $algo) use ($unsigned_token, $cert): string {
            $private_key = openssl_pkey_get_private($cert);
            openssl_sign($unsigned_token, $signature, $private_key, $algo);
            openssl_free_key($private_key);
            return $signature;
        };

        $encoded_str = match ($this) {
            self::HS256 => $hmac_encoder('sha256'),
            self::HS384 => $hmac_encoder('sha384'),
            self::HS512 => $hmac_encoder('sha512'),
            self::RS256 => $openssl_encoder(OPENSSL_ALGO_SHA256),
            self::RS384 => $openssl_encoder(OPENSSL_ALGO_SHA384),
            self::RS512 => $openssl_encoder(OPENSSL_ALGO_SHA512),
            self::ES256 => $openssl_encoder(OPENSSL_ALGO_SHA256),
            self::ES384 => $openssl_encoder(OPENSSL_ALGO_SHA384),
            self::ES512 => $openssl_encoder(OPENSSL_ALGO_SHA512),
        };

        return Url::base64UrlEncode($encoded_str);
    }
}
