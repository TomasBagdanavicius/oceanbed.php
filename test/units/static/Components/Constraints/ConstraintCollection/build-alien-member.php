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
use LWP\Components\Definitions\MaxDefinition;
use LWP\Common\Collections\Exceptions\InvalidMemberException;

$min_size_constraint = new MinSizeConstraint(10);
$in_set_constraint = new InSetConstraint(['foo', 'bar', 'baz']);
$max_definition = new MaxDefinition(100);

$collection = new ConstraintCollection();
$collection->add($min_size_constraint);

try {
    $collection->add($max_definition);
    $result = false;
} catch (InvalidMemberException $exception) {
    $result = true;
}

$collection->add($in_set_constraint);

Demo\assert_true(
    $result,
    "Collection incorrectly accepted alien member"
);
