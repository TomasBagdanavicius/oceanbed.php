<?php

declare(strict_types=1);

include __DIR__ . '/../../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');

use LWP\Network\Uri\Url;
use LWP\Network\CurlWrapper\Request;
use LWP\Network\Http\RequestMessage;
use LWP\Network\Http\HttpMethodEnum;

$url = new Url('https://www.lwis.net/bin/Text/en-pangram.txt');

$options = [
    'headers' => [
        'X-Custom' => 'custom-header-value',
    ],
];

$request = new Request(HttpMethodEnum::GET, $url, $options);

// A typical way to set a Curl option.
$request->setCurlOption(CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_2);

// Trying to set an easy Curl option. This should fail.
try {
    $request->easyCurlOption('dummy', "Hello World!");
} catch (Throwable $exception) {
    print "Expected error: " . $exception->getMessage() . PHP_EOL;
}

// Confident that this Curl option is valid, hence no "try" block.
$request->easyCurlOption('useragent', "Custom Agent");

// A way to check if a specific option was set.
var_dump($request->getOptions()->containsKey('headers'));

// Gets all set options.
#print_r( $request->getAllCurlOptions() );

// For debugging, gets all set options with constant name as the key name.
print_r($request->getLabeledCurlOptions());
