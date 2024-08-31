<?php

declare(strict_types=1);

include __DIR__ . '/../../../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');

use LWP\Network\Http\Auth\Bearer;

$token = 'AbCdEf123456';

$auth_basic = new Bearer($token);

print "Signature: ";
var_dump($auth_basic->buildSignature());
print "Request header: ";
var_dump($auth_basic->buildHeader());
