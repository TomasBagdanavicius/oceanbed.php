<?php

declare(strict_types=1);

include __DIR__ . '/../../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');

use LWP\Network\CurlWrapper\Handle;
use LWP\Network\CurlWrapper\HandleCollection;
use LWP\Network\Uri\Uri;

$requests_count = 0;

/* First request: Domain 1 */

$handle_collection = new HandleCollection();

$handle = $handle_collection->getByRemoteSocket(new Uri('ssl://www.lwis.net:443'));

$handle->occupyFromCurlOptionsSet([
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HEADER => true,
    CURLINFO_HEADER_OUT => true,
    CURLOPT_URL => 'https://www.lwis.net/bin/Text/hello-world.txt',
    CURLOPT_HTTPHEADER => [
        'Connection: Keep-Alive',
    ],
]);

$curl_handle = $handle->getHandle();
$response = curl_exec($curl_handle);
print curl_getinfo($curl_handle, CURLINFO_HEADER_OUT);
print_r($response);
$handle->reset();

$requests_count++;

echo str_repeat(PHP_EOL, 3);


/* Second request: Domain 2 */

// Manually create a new member, because the first one shouldn't be overwritten. The first one is meant to be reused below.
$handle = $handle_collection->createNewMember();

$handle->occupyFromCurlOptionsSet([
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HEADER => true,
    CURLINFO_HEADER_OUT => true,
    CURLOPT_URL => 'http://www.lwis.net/bin/Text/hello-world.txt',
    CURLOPT_HTTPHEADER => [
        'Connection: Keep-Alive',
    ],
]);

$curl_handle = $handle->getHandle();
$response = curl_exec($curl_handle);
print curl_getinfo($curl_handle, CURLINFO_HEADER_OUT);
print_r($response);
$handle->reset();

$requests_count++;

echo str_repeat(PHP_EOL, 3);


/* Third request: Domain 1 */

$handle = $handle_collection->getByRemoteSocket(new Uri('ssl://www.lwis.net:443'));

$handle->occupyFromCurlOptionsSet([
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HEADER => true,
    CURLINFO_HEADER_OUT => true,
    CURLOPT_URL => 'https://www.lwis.net/bin/Text/en-pangram.txt',
    CURLOPT_HTTPHEADER => [
        'Connection: Keep-Alive',
    ],
]);

$curl_handle = $handle->getHandle();
$response = curl_exec($curl_handle);
print curl_getinfo($curl_handle, CURLINFO_HEADER_OUT);
print_r($response);
$handle->reset();

$requests_count++;

echo str_repeat(PHP_EOL, 3);


/* Fourth request: Domain 2 */

$handle = $handle_collection->getByRemoteSocket(new Uri('tcp://www.lwis.net:80'));

$handle->occupyFromCurlOptionsSet([
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HEADER => true,
    CURLINFO_HEADER_OUT => true,
    CURLOPT_URL => 'http://www.lwis.net/bin/Text/en-pangram.txt',
    CURLOPT_HTTPHEADER => [
        'Connection: Keep-Alive',
    ],
]);

$curl_handle = $handle->getHandle();
$response = curl_exec($curl_handle);
print curl_getinfo($curl_handle, CURLINFO_HEADER_OUT);
// Keep an eye on the "Keep-Alive" response header. It should have one "max" count number deducted.
print_r($response);
$handle->reset();

$requests_count++;

echo str_repeat(PHP_EOL, 3);


/* Statistics */

print "Total requests: " . $requests_count . PHP_EOL;
print "Total handles used: " . $handle_collection->count() . PHP_EOL;
