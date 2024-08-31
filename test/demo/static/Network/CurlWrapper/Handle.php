<?php

declare(strict_types=1);

include __DIR__ . '/../../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');

use LWP\Network\CurlWrapper\Handle;

$handle = new Handle();

var_dump($handle->getId());
var_dump($handle->getHandle());

$handle->occupyFromCurlOptionsSet([
    /* Required */
    CURLOPT_URL => 'https://www.lwis.net/bin/Text/en-pangram.txt',
    /* Compulsory */
    CURLOPT_RETURNTRANSFER => true,
]);

var_dump($handle->getIndexableData());

$response = curl_exec($handle->getHandle());

print $response;
