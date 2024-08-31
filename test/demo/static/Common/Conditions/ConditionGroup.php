<?php

declare(strict_types=1);

include __DIR__ . '/../../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');

use LWP\Common\Conditions\Condition;
use LWP\Common\Conditions\ConditionGroup;
use LWP\Common\Conditions\Enums\ConditionComparisonOperatorsEnum;
use LWP\Common\Enums\NamedOperatorsEnum;
use LWP\Components\Definitions\DefinitionCollectionSet;
use LWP\Components\Model\BasePropertyModel;

/* Condition Stringify Replacer */

$stringify_replacer = function (Condition $condition): ?string {
    return sprintf(
        "%s %s %s",
        ("'" . $condition->keyword . "'"),
        $condition->control_operator->value,
        $condition->value
    );
};

$condition_1 = new Condition('one', 1, ConditionComparisonOperatorsEnum::EQUAL_TO);
$condition_2 = new Condition('two', 4, ConditionComparisonOperatorsEnum::EQUAL_TO);
$condition_3 = new Condition('three', 3, ConditionComparisonOperatorsEnum::EQUAL_TO);

$condition_group_1 = new ConditionGroup(name: 'group1');
$condition_group_1->add($condition_2);
$condition_group_1->add($condition_3, NamedOperatorsEnum::OR);

$condition_group_root = new ConditionGroup();
#$condition_group_root = new ConditionGroup(stringify_replacer: $stringify_replacer);
$condition_group_root->add($condition_1);
$condition_group_root->add($condition_group_1, NamedOperatorsEnum::AND);
#$condition_group_root->setFlags($condition_group_root::PRINT_OPERATORS_SYMBOLS);

var_dump($condition_group_root->__toString());

/* Match Array */

$array = [
    'one' => '1',
    'three' => '3',
];

echo "Match array: ";
var_dump($condition_group_root->matchArray($array));

echo "Match array and return matching elems: ";
var_dump($condition_group_root->matchArray($array, return_elems: true));

/* Match Model */

$definition_collection_set = DefinitionCollectionSet::fromArray([
    'one' => [
        'type' => 'integer',
    ],
    'three' => [
        'type' => 'integer',
    ],
]);

$base_property_model = BasePropertyModel::fromDefinitionCollectionSet($definition_collection_set);
$base_property_model->one = 1;
$base_property_model->three = 3;

echo "Match model: ";
var_dump($condition_group_root->matchModel($base_property_model));

/* From Array */

$array = [
    'one' => 1,
    'group_1' => [ // Named group.
        'two' => 2,
        'three' => 3,
        [ // Unnamed group.
            'foo' => 'bar',
            'bar' => 'baz',
        ],
    ],
    'four' => 4,
];

$condition_group = ConditionGroup::fromArray($array, default_named_operator: NamedOperatorsEnum::AND);
prl($condition_group->__toString());

/* Named Map */

// Gets nested group object from the root level.
#prl( $condition_group_root->getInnerGroupByName('group1')::class );

/* Get Values */

pr($condition_group_root->getValues());
