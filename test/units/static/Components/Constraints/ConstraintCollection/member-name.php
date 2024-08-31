<?php

declare(strict_types=1);

include __DIR__ . '/../../../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');

use LWP\Components\Constraints\ConstraintCollection;
use LWP\Components\Constraints\MinSizeConstraint;

$min_size_constraint = new MinSizeConstraint(10);

$collection = new ConstraintCollection();
$index_name = $collection->add($min_size_constraint);

Demo\assert_true(
    $index_name === MinSizeConstraint::class,
    "Incorrect index name"
);
