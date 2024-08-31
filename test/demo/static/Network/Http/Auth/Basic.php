<?php

declare(strict_types=1);

include __DIR__ . '/../../../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');

use LWP\Network\Http\Auth\Basic;

$username = 'user';
$password = 'password';

$auth_basic = new Basic($username, $password);

print "Signature: ";
var_dump($auth_basic->buildSignature());

print "Request header: ";
var_dump($auth_basic->buildHeader());
