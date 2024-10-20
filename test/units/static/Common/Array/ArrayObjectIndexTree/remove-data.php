<?php

declare(strict_types=1);

include __DIR__ . '/../../../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');

use LWP\Common\Array\ArrayObjectIndexTree;
use LWP\Common\Conditions\Condition;
use LWP\Common\Conditions\Enums\ConditionComparisonOperatorsEnum;

$index_tree = new ArrayObjectIndexTree();
$entry_1 = [
    'id' => 1,
    'msg' => 'There has been an error.',
    'type' => 'error',
    'origin' => 'session',
];
$index_id_1 = $index_tree->add($entry_1, 'index1');

$index_tree->removeData($index_id_1, $entry_1);

Demo\assert_true(
    $index_tree->dataIndexExists('index1') === false,
    "Unexpected result"
);
