<?php

declare(strict_types=1);

include __DIR__ . '/../../../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');

use LWP\Network\Http\Cookies\Cookies;
use LWP\Common\String\Str;
use LWP\Network\Http\Server;

$step = ($_GET['step'] ?? 1);

if ($step == '1') {

    $name = Str::random(12, '0-9a-zA-Z');
    $value = Str::random(12, '0-9a-zA-Z');

    // Set single cookie.

    Cookies::set($name, $value, [
        'max-age' => 10,
    ]);

    $url = Server::getCurrentUrl();
    $query_component = $url->getQueryComponent();
    $query_component->set('name', $name);
    $query_component->set('value', $value);
    $query_component->set('step', 2);

    header("Location: " . $url);

} elseif ($step == '2') {

    if (isset($_COOKIE[$_GET['name']])) {

        Cookies::remove($_GET['name']);

        $url = Server::getCurrentUrl();
        $query_component = $url->getQueryComponent();
        $query_component->replace('step', '3');

        header("Location: " . $url);

    } else {

        throw new Exception("Could not set cookie.");
    }

} elseif ($step == '3') {

    if (!isset($_COOKIE[$_GET['name']])) {

        print "All tests were successful.";

    } else {

        throw new Exception("Could not unset cookie.");
    }
}
