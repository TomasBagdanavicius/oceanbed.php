<?php

declare(strict_types=1);

include __DIR__ . '/../../../../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');

use LWP\Network\Http\Auth\Enums\JwtAlgorithmEnum;

$secret_key = 'h6fWhn23kiL';

echo "HMAC-SHA-256 encoded string: ";
vare(JwtAlgorithmEnum::HS256->encode("Secret text", $secret_key));
