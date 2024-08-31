<?php

declare(strict_types=1);

include __DIR__ . '/../../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');

use LWP\Common\Crypto\SaferCrypto;

$safer_crypto = new SaferCrypto();

$key = $safer_crypto->generateKey();
print "Key: ";
var_dump($key);

$encrypted = $safer_crypto->encrypt("Hello World!", $key, true);
print "Encrypted: ";
var_dump($encrypted);

$decrypted = $safer_crypto->decrypt($encrypted, $key, true);
print "Decrypted: ";
var_dump($decrypted);
