<?php

declare(strict_types=1);

include __DIR__ . '/../../../../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');

use LWP\Components\Model\RelationalPropertyModel;
use LWP\Components\Properties\RelationalProperty;
use LWP\Components\Model\SharedAmounts\{
    MaxSumSharedAmount,
    SharedAmountCollection
};

// Alias and shared amount.

$relational_model = new RelationalPropertyModel();

$max_sum_shared_amount = new MaxSumSharedAmount(10);
$shared_amount_collection = new SharedAmountCollection(
    'group_1',
    "Group 1 description."
);
$shared_amount_collection->add($max_sum_shared_amount);

$relational_model->setSharedAmountGroup($shared_amount_collection);

$relational_property_1 = new RelationalProperty(
    $relational_model,
    'prop_1',
    'integer'
);
$relational_property_2 = new RelationalProperty(
    $relational_model,
    'prop_2',
    'integer'
);

$relational_property_2->assignSharedAmountGroup($shared_amount_collection);

// Prop 2 is an alias of prop 1.
$relational_property_2->setupRelation('alias', 'prop_1');

// No exception, because error handling mode is COLLECT_VALUES.
$relational_model->prop_1 = 20;

// Prop 2 is in "out of bounds" error.
Demo\assert_true(
    $relational_property_2->hasErrors(),
    "Property 2 must be in out of bounds state"
);
