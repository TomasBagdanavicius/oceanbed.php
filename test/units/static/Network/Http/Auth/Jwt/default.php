<?php

declare(strict_types=1);

include __DIR__ . '/../../../../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');

use LWP\Network\Http\Auth\Jwt;
use LWP\Network\Http\Auth\Enums\JwtAlgorithmEnum;

$payload = [
    'id' => 2,
    // Thursday, August 22, 2030 4:21:28 PM GMT
    'exp' => 1913646088000,
];
$secret_key = 'gh8dJns9iKlWeCX0cvkwe5ScfdD0ikSlSqWxDSkLfi6DbkxW';

$jwt = new Jwt(JwtAlgorithmEnum::HS256, $payload, $secret_key);

Demo\assert_true($jwt instanceof Jwt, "Unexpected result");
