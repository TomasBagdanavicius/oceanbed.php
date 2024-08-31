<?php

declare(strict_types=1);

namespace LWP\Common\Crypto;

use LWP\Common\Crypto\BasicCrypto;

class SaferCrypto extends BasicCrypto
{
    public const HASH_ALGORITHM = 'sha256';


    // Encrypts a message (with authentication).

    public static function encrypt(string $str, string $key, bool $base_encode = false): string
    {

        list($encoding_key, $auth_key) = self::splitKeys($key);

        $cipher_text = parent::encrypt($str, $encoding_key);

        // Calculate a MAC of the IV and ciphertext
        $mac = hash_hmac(self::HASH_ALGORITHM, $cipher_text, $auth_key, true);

        return (!$base_encode)
            ? ($mac . $cipher_text)
            : base64_encode($mac . $cipher_text);
    }


    // Decrypts a message (with authentication).

    public static function decrypt(string $str, string $key, bool $base_encoded = false): string
    {

        if ($base_encoded) {

            $str = parent::baseDecodeStrict($str);
        }

        list($encoding_key, $auth_key) = self::splitKeys($key);

        // split concatenated mac and cypher text
        $hash_length = mb_strlen(hash(self::HASH_ALGORITHM, '', true), '8bit');
        $mac = mb_substr($str, 0, $hash_length, '8bit');
        $cipher_text = mb_substr($str, $hash_length, null, '8bit');

        $calculated = hash_hmac(
            self::HASH_ALGORITHM,
            $cipher_text,
            $auth_key,
            true
        );

        if (!hash_equals($mac, $calculated)) {

            throw new \Exception('Decryption failure');
        }

        return parent::decrypt($cipher_text, $encoding_key);
    }


    // Splits a master key into two hash keys.

    protected static function splitKeys(string $master_key)
    {

        // use HKDF!
        return [
            hash_hmac(self::HASH_ALGORITHM, 'ENCRYPTION', $master_key, true),
            hash_hmac(self::HASH_ALGORITHM, 'AUTHENTICATION', $master_key, true)
        ];
    }
}
