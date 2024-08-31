<?php

declare(strict_types=1);

include __DIR__ . '/../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');

use LWP\Common\Criteria;
use LWP\Common\Conditions\Enums\ConditionComparisonOperatorsEnum;
use LWP\Common\Conditions\Condition;
use LWP\Common\Conditions\ConditionGroup;
use LWP\Common\Enums\NamedOperatorsEnum;
use LWP\Common\String\Clause\SortByComponent;

$criteria = new Criteria();

$condition = new Condition('name', 'John', ConditionComparisonOperatorsEnum::EQUAL_TO);

$condition_group = new ConditionGroup();
$condition_group->add(new Condition('age', 30, ConditionComparisonOperatorsEnum::LESS_THAN_OR_EQUAL_TO));
$condition_group->add(new Condition('occupation', 'Engineer', ConditionComparisonOperatorsEnum::EQUAL_TO));

$criteria
    ->condition($condition)
    ->sort(SortByComponent::fromString('name ASC, city DESC'))
    ->limit(10)
    ->condition($condition_group, NamedOperatorsEnum::OR)
    ->offset(2);

echo "To string: ";
var_dump($criteria->__toString());

echo "Condition group: ";
var_dump($criteria->base_condition_group::class);

if ($sort_by = $criteria->getSort()) {
    echo "Sort by: ";
    var_dump((is_callable($sort_by)) ? $sort_by : $sort_by::class);
}

echo "Limit: ";
var_dump($criteria->getLimit());
