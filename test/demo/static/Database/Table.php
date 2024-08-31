<?php

declare(strict_types=1);

include __DIR__ . '/../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');
require_once(Demo\TEST_PATH . '/database-link-test.php');

use LWP\Common\Conditions\Condition;
use LWP\Common\Conditions\ConditionGroup;
use LWP\Common\Enums\NamedOperatorsEnum;
use LWP\Database\Table;
use LWP\Database\Result;
use LWP\Filesystem\Path\PosixPath;

$table = $database->getTable('temp');

echo "Name: ";
var_dump($table->table_name);

echo "Column list: ";
print_r($table->getColumnList());

echo "Has column: ";
var_dump($table->hasColumn('title'));

echo "Auto increment: ";
var_dump($table->getAutoIncrement());

echo "Find columns: ";
print_r($table->findColumns(['id', 'title', 'foo']));

echo "Supports multi-query: ";
var_dump($table->supportsMultiQuery());

echo "Get column by: ";
var_dump($table->getColumnBy('title', 'id', 1)->toArray());

echo "Get columns by: ";
var_dump($table->getColumnsBy(['id', 'title', 'custom'], 'id', 1)->toArray());

#$table->lock();
#$table->lock();$table->unlock();
#var_dump( $table->resetAutoIncrement() );
#var_dump( $table->getMaxValueByColumn('title') );
#var_dump( $table->getColumnCount() );
#print_r( $table->getColumnDefinitionDataArray() );
#print_r( $table->formatFieldListWithBrackets(['one', 'two', 'three',]) );
#$table->getSelectedAllResult(['id', 'title']);
#var_dump( $table->allColumnsExist(['title', 'id']) );
#$table->truncate();
#var_dump( $table->getColumnProperty('title') );
#var_dump( $table->getPrimaryContainerName() );
#var_dump( $table->isContainerPrimary('id') );
#var_dump( $table->containsContainerValue('custom', 'C3') );
#var_dump( $table->containsContainerValues('custom', ['C1', 'C2', 'C3']) );
#var_dump( $table->updateIntegerContainerValue('custom_4', 9, 10) );

/*$file_path = PosixPath::getFilePathInstance('../tmp/table-export.csv');
$table->putDataToCsvFile($file_path, ['id', 'title']);*/

/* Insert */

/*$table->insert([
    'title' => 'Insert Single',
    'custom' => 'Single',
]);*/

/* Insert Multi */

/*$multi_insert = $table->insertMulti([
    [
        'title' => 'Insert Multi 1',
        'custom' => 'Multi 1',
    ],[
        'title' => 'Insert Multi 2',
        'custom' => 'Multi 2',
    ]
]);

print_r($multi_insert);*/

/* $condition = new Condition('custom', 'C1');
$condition_group = ConditionGroup::fromCondition($condition);
$condition_group->add(new Condition('custom_4', '3'));
$table->byConditionObject($condition_group); */

/* Delete */

/* echo "Delete by multiple columns: ";
var_dump($table->deleteByMultipleColumns([
    'custom' => 'C4',
    'custom_4' => '4',
])); */

/* echo "Delete by multiple field values: ";
var_dump($table->deleteByMultipleFieldValues('custom_4', ['4', '5'])); */

/* echo "Delete by condition object: ";
$condition_1 = new Condition('custom_4', '2');
$condition_2 = new Condition('custom', 'C1');
$condition_group = new ConditionGroup;
$condition_group->add($condition_1);
$condition_group->add($condition_2, NamedOperatorsEnum::OR);
var_dump($table->deleteByConditionObject($condition_group)); */
