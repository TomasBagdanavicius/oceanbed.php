<?php

declare(strict_types=1);

include __DIR__ . '/../../../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');

use LWP\Network\Http\Auth\JwtParser;
use LWP\Common\Exceptions\ExpiredException;
use LWP\Network\Http\Auth\Exceptions\InvalidJwtTokenSignature;
use LWP\Network\Http\Auth\Exceptions\InvalidJwtTokenException;

$signed_token = "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpZCI6MiwiZXhwIjoxOTEzNjQ2MDg4MDAwfQ.m8126gyzv3HC93acHsq5J6FddoDcoyCZvLo6SZwl2VA";
$secret_key = 'gh8dJns9iKlWeCX0cvkwe5ScfdD0ikSlSqWxDSkLfi6DbkxW';

try {
    $jwt_parser = new JwtParser($signed_token, $secret_key);
    // Possible exceptions
} catch (InvalidJwtTokenException|ExpiredException|InvalidJwtTokenSignature $exception) {
    // Just rethrow
    throw $exception;
}

echo "Algorithm: ";
var_dump($jwt_parser->getAlgorithm());

echo "Payload: ";
var_dump($jwt_parser->payload);
