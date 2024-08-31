<?php

declare(strict_types=1);

include __DIR__ . '/../../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');

use LWP\Common\Crypto\BasicCrypto;

$basic_crypto = new BasicCrypto();

$key = $basic_crypto->generateKey();
print "Key: ";
var_dump($key);

$encrypted = $basic_crypto->encrypt("Hello World!", $key, true);
print "Encrypted: ";
var_dump($encrypted);

$decrypted = $basic_crypto->decrypt($encrypted, $key, true);
print "Decrypted: ";
var_dump($decrypted);
