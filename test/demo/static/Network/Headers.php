<?php

declare(strict_types=1);

include __DIR__ . '/../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');

use LWP\Network\Headers;

$primary_headers = [
    'date' => date('Y-m-d\TH:i:s'),
    'server' => 'Apache',
    'x-powered-by' => 'PHP',
    // Allows multiple values in the constructor array.
    'set-cookie' => [
        'session_id=123',
        'name=value',
    ],
];

$headers = new Headers($primary_headers);

// Set a new header field.
var_dump($headers->set('connection', 'keep-alive'));

// Add a second header field value, when the header field name already exists.
var_dump($headers->set('x-powered-by', 'LWP'));

// Allows array values.
var_dump($headers->set('x-powered-by', ['MySQL', 'cURL']));

// Export to array.
print_r($headers->toArray());

// Gets the string size of the headers.
var_dump($headers->getSize());

// Export to string.
echo $headers->__toString();

foreach ($headers as $name => $value) {
    var_dump($name, $value);
}
