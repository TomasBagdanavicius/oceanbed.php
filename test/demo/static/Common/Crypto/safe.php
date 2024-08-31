<?php

declare(strict_types=1);

include __DIR__ . '/../../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');

use LWP\Common\Crypto\SafeCrypto;

$safe_crypto = new SafeCrypto();
echo "Is enabled: ";
var_dump($safe_crypto::isEnabled());

$key = $safe_crypto->generateKey();
echo "Key: ";
var_dump($key);

$encrypted = $safe_crypto->encrypt("Hello World!", $key, true);
echo "Encrypted: ";
var_dump($encrypted);

$decrypted = $safe_crypto->decrypt($encrypted, $key, true);
echo "Decrypted: ";
var_dump($decrypted);
