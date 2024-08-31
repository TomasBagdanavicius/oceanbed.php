<?php

declare(strict_types=1);

namespace LWP\Common\Crypto;

use LWP\Common\Crypto\SaferCrypto;

class SafeCrypto extends SaferCrypto
{
    // Gets random bytes for key.

    public static function generateKey(int $length = 32): string
    {

        return sodium_crypto_secretbox_keygen();
    }


    // Generates random bytes for a readable key.

    public static function generateToken(int $length = 78): string
    {

        return bin2hex(
            random_bytes($length)
        );
    }


    // Safely encrypts a message.

    public static function encrypt(string $str, string $key, bool $base_encode = false): string
    {

        $nonce = random_bytes(SODIUM_CRYPTO_SECRETBOX_NONCEBYTES);

        $cipher_text = ($nonce . sodium_crypto_secretbox($str, $nonce, $key));

        if (extension_loaded('sodium') && function_exists('sodium_memzero')) {

            sodium_memzero($str);
            sodium_memzero($key);
        }

        return (!$base_encode)
            ? $cipher_text
            : base64_encode($cipher_text);
    }


    // Safely decrypts a message.

    public static function decrypt(string $str, string $key, bool $base_encoded = false): string
    {

        if ($base_encoded) {

            $str = parent::baseDecodeStrict($str);
        }

        if (mb_strlen($str, '8bit') < (SODIUM_CRYPTO_SECRETBOX_NONCEBYTES + SODIUM_CRYPTO_SECRETBOX_MACBYTES)) {

            throw new \Exception("Decryption failure.");
        }

        $nonce = mb_substr($str, 0, SODIUM_CRYPTO_SECRETBOX_NONCEBYTES, '8bit');
        $cipher_text = mb_substr($str, SODIUM_CRYPTO_SECRETBOX_NONCEBYTES, null, '8bit');

        $plain_text = sodium_crypto_secretbox_open($cipher_text, $nonce, $key);

        if ($plain_text === false) {

            throw new \Exception("Decryption failure.");
        }

        if (extension_loaded('sodium') && function_exists('sodium_memzero')) {

            sodium_memzero($cipher_text);
            sodium_memzero($key);
        }

        return $plain_text;
    }


    // Checks if the sodium extension is enabled.

    public static function isEnabled(): bool
    {

        return extension_loaded('sodium');
    }
}
