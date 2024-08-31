<?php

declare(strict_types=1);

include __DIR__ . '/../../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');

use LWP\Common\Criteria;
use LWP\Common\Array\IndexableArrayCollection;
use LWP\Common\Conditions\Enums\ConditionComparisonOperatorsEnum;
use LWP\Common\Conditions\Condition;
use LWP\Common\Conditions\ConditionGroup;
use LWP\Common\Enums\NamedOperatorsEnum;

$primary_set = [
    [
        'id' => 1,
        'name' => 'John',
        'age' => 35,
        'occupation' => 'Teacher',
        'height' => 1.92,
    ],
];

$collection = new IndexableArrayCollection($primary_set, two_level_tree_support: true);

$index1 = $collection->add([
    'id' => 3,
    'name' => 'Jane',
    'age' => 52,
    'occupation' => 'Lawyer',
    'height' => 1.71,
]);

$index2 = $collection->add([
    'id' => 4,
    'name' => 'Dave',
    'age' => 41,
    'occupation' => [
        'Teacher',
        'Engineer',
    ],
    'height' => 1.79,
]);

$index3 = $collection->add([
    'id' => 10,
    'name' => 'John',
    'age' => 31,
    'occupation' => 'Architect',
    'height' => 1.88,
]);

/* Make Updates */

// Update entire entry.
$collection->update($index1, [
    'id' => 3,
    'name' => 'Liz',
    'age' => 52,
    'occupation' => 'Programmer',
    'height' => 1.73,
]);

// Update a single value.
$collection->updateValue($index3, 'occupation', 'Pharmacist');
$collection->updateValue($index3, 'height', 1.86);

// Check the updated dataset.
#print_r( $collection->toArray() );

// Debug the index tree.
#print_r( $collection->getIndexTree()->getTree() );


/* Match by Single Condition */

$condition = new Condition('name', 'John');
$filtered_collection = $collection->matchCondition($condition);
print_r($filtered_collection->toArray());


/* Match by Condition Group */

$condition_1 = new Condition('name', 'Liz');
$condition_2 = new Condition('name', 'Dave');
$condition_3 = new Condition('age', 35, ConditionComparisonOperatorsEnum::GREATER_THAN_OR_EQUAL_TO);

$condition_group = new ConditionGroup();
$condition_group->add($condition_1);
$condition_group->add($condition_2, NamedOperatorsEnum::OR);
$condition_group->add($condition_3, NamedOperatorsEnum::OR);

$filtered_collection = $collection->matchConditionGroup($condition_group);

print_r($filtered_collection->toArray());


/* Match by Criteria */

/* Filter out all Johns and sort them by occupation in ascending order. */

$criteria = (new Criteria())
    ->condition(new Condition('name', 'John'))
    ->sort('occupation ASC');

$filtered_collection = $collection->matchCriteria($criteria);

print_r($filtered_collection->toArray());

/* Filter out all teachers, sort them by name in ascending order, and set a limit to 1 entry. */

$criteria = (new Criteria())
    ->condition(new Condition('occupation', 'Teacher'))
    ->sort('name ASC')
    ->limit(1);

$filtered_collection = $collection->matchCriteria($criteria);

print_r($filtered_collection->toArray());
