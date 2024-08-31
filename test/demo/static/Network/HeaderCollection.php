<?php

declare(strict_types=1);

include __DIR__ . '/../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');

use LWP\Network\Headers;
use LWP\Network\HeaderCollection;
use LWP\Common\Criteria;

$headers_set_1 = new Headers([
    'date' => date('Y-m-d\TH:i:s'),
    'server' => 'Apache',
    'x-powered-by' => 'PHP',
    'cookie' => 'session_id=123',
]);

$headers_set_2 = new Headers([
    'date' => date('Y-m-d\TH:i:s'),
    'server' => 'Nginx',
    'x-powered-by' => 'PHP',
    'cookie' => [
        'foo=bar',
        'key=value',
    ],
]);

$headers_collection = new HeaderCollection();

var_dump($headers_collection->add($headers_set_1));
var_dump($headers_collection->add($headers_set_2));

// Debug index tree.
#print_r( $headers_collection->getIndexableArrayCollection()->getIndexTree()->getTree() );

/* Filter out headers that contain "server: Apache" header field. */

$filtered_collection = $headers_collection->matchBySingleCondition('server', 'Apache');

foreach ($filtered_collection as $headers) {

    print_r($headers->toArray());
}

/* Filter out headers that contain "cookie: foo=bar" header field. */

$filtered_collection = $headers_collection->matchBySingleCondition('cookie', 'foo=bar');

foreach ($filtered_collection as $headers) {

    print_r($headers->toArray());
}
