<?php

declare(strict_types=1);

include __DIR__ . '/../../../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');

use LWP\Common\Aggregators\NumberAggregator;

$number_aggregator = new NumberAggregator();

$number_aggregator->set(3);
$number_aggregator->set(7.5);

Demo\assert_true(
    $number_aggregator->getTimesSetCount() === 2,
    "Unexpected result"
);
