<?php

declare(strict_types=1);

include __DIR__ . '/../../../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');

use LWP\Network\Http\Auth\MAC;
use LWP\Network\Uri\Uri;
use LWP\Network\Http\HttpMethodEnum;

$uri = new Uri('https://www.domain.com/dir/index.html');
$http_method = HttpMethodEnum::GET;
$extension_string = 'a,b,c';

$auth_basic = new MAC($uri, $http_method, $extension_string);

print "Signature: ";
var_dump($auth_basic->buildSignature());

print "Request header: ";
var_dump($auth_basic->buildHeader());
