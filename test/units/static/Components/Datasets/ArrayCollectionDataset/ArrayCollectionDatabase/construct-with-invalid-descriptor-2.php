<?php

declare(strict_types=1);

include __DIR__ . '/../../../../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');
include(Demo\TEST_PATH . '/demo/static/Components/Datasets/ArrayCollectionDataset/shared/data.php');

use LWP\Components\Datasets\ArrayCollectionDataset\ArrayCollectionDatabase;

try {
    $database = new ArrayCollectionDatabase(UnexistingClass::class);
    $result = false;
} catch (\ValueError) {
    $result = true;
}

Demo\assert_true($result, "Constructor incorrectly accepted invalid descriptor class");
