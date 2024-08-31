<?php

declare(strict_types=1);

include __DIR__ . '/../../../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');

use LWP\Network\Http\Auth\Digest;
use LWP\Network\Headers;
use LWP\Network\Uri\UriReference;
use LWP\Network\Http\HttpMethodEnum;

$auth_digest_response_header_field = 'Digest realm="Digest Protected Area", nonce="0f65a56f17bfa301f7b93c328264dda4", qop="auth", opaque="3543acfc4724f106aa730086b4dbdcfd", algorithm=MD5, stale=FALSE';

$header_field_auth_data = Headers::parseWWWAuthenticate($auth_digest_response_header_field);

$username = 'user';
$password = 'password';
$params = $header_field_auth_data['params'];
$http_method = HttpMethodEnum::GET;
$uri = new UriReference('/dir/index.html');
$counter = 1;

$auth_basic = new Digest($username, $password, $params, $http_method, $uri, $counter);

print "Signature: ";
var_dump($auth_basic->buildSignature());
print PHP_EOL;
print "Request header: ";
var_dump($auth_basic->buildHeader());
