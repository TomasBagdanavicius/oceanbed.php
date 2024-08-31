<?php

declare(strict_types=1);

include __DIR__ . '/../../../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');

use LWP\Network\Http\Cookies\Cookies;
use LWP\Network\Http\Server;

// Basic.
Cookies::set('foo', 'bar');

// Add options.
/*Cookies::set('foo', 'bar', [
    'httponly' => true,
]);*/

print "Please visit the page below to view accepted cookies:" . PHP_EOL;

$url = Server::getCurrentUrl();
$path_component = $url->getPathComponent();
$path_component->setBasename('view-cookies.php');

print $url;
