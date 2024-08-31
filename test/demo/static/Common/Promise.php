<?php

declare(strict_types=1);

include __DIR__ . '/../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');

use LWP\Common\Promise\Promise;

$promise = new Promise(function () {
    return "Executed.";
}, function () {
    return "Canceled.";
});

$promise->addCallbacks(
    function ($result) {
        var_dump($result);
    },
    function ($exception) {
        var_dump("First rejection: " . $exception->getMessage());
    }
);

$promise->addCallbacks(
    function ($result) {
        if ($result == "Executed.") {
            throw new \Exception("Not good enough.");
        }
    },
    function ($exception) {
        var_dump("Another rejection: " . $exception->getMessage());
    }
);

$promise->process();
