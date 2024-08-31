<?php

declare(strict_types=1);

namespace LWP\Common\Crypto;

use Exception;

class BasicCrypto
{
    public const METHOD = 'AES-128-CBC';


    // Generates an encryption key.

    public static function generateKey(int $length = 32): string
    {

        return base64_encode(
            openssl_random_pseudo_bytes($length)
        );
    }


    // Generates random bytes for a readable key.

    public static function generateToken(int $length = 32): string
    {

        return bin2hex(
            openssl_random_pseudo_bytes($length)
        );
    }


    // Generates an initialization vector.

    public static function generateNonce()
    {

        return openssl_random_pseudo_bytes(
            openssl_cipher_iv_length(self::METHOD)
        );
    }


    // Encrypts a message (no authentication).

    public static function encrypt(string $str, string $key, bool $base_encode = false): string
    {

        $nonce = self::generateNonce();

        // Encrypt $data using the chosen method/cipher with the given encryption key and
        // our initialization vector
        $cipher_text = openssl_encrypt(
            $str,
            self::METHOD,
            $key,
            OPENSSL_RAW_DATA,
            $nonce
        );

        // Concatenate the cypher text and the nonce to be used in decryption.
        return (!$base_encode)
            ? ($nonce . $cipher_text)
            : base64_encode($nonce . $cipher_text);
    }


    // Decrypts a message (no authentication).

    public static function decrypt(string $str, string $key, bool $base_encoded = false): string
    {

        if ($base_encoded) {

            $str = self::baseDecodeStrict($str);
        }

        // split the concatenated cypther text and nonce by calculating offset at which they were joined
        // + instead of exploding by a certain delimiter
        $nonce_length = openssl_cipher_iv_length(self::METHOD);
        $nonce = mb_substr($str, 0, $nonce_length, '8bit');
        $cipher_text = mb_substr($str, $nonce_length, null, '8bit');

        return openssl_decrypt(
            $cipher_text,
            self::METHOD,
            $key,
            OPENSSL_RAW_DATA,
            $nonce
        );
    }


    // Attempt to base decode a string in strict mode.

    public static function baseDecodeStrict(string $str): string
    {

        $str = base64_decode($str, true); // strict

        if ($str === false) {

            throw new \RuntimeException("Decryption failure.");
        }

        return $str;
    }
}
