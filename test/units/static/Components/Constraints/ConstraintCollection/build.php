<?php

declare(strict_types=1);

include __DIR__ . '/../../../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');

use LWP\Components\Constraints\ConstraintCollection;
use LWP\Components\Constraints\MinSizeConstraint;
use LWP\Components\Constraints\InSetConstraint;

$min_size_constraint = new MinSizeConstraint(10);
$in_set_constraint = new InSetConstraint(['foo', 'bar', 'baz']);

$collection = new ConstraintCollection();
$collection->add($min_size_constraint);
$collection->add($in_set_constraint);

Demo\assert_true(
    $collection->count() === 2,
    "Could not build collection"
);
