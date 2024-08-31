<?php

declare(strict_types=1);

include __DIR__ . '/../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');
require_once(Demo\TEST_PATH . '/database-link-test.php');

use LWP\Database\Table;
use LWP\Components\Datasets\Attributes\SelectAllAttribute;
use LWP\Common\Conditions\Condition;
use LWP\Common\Conditions\ConditionGroup;

$dataset = $database->getTable('static');
$select_handle = $dataset->getSelectHandle();
$fetch_manager = $dataset->getFetchManager();

echo "Class name: ";
var_dump($fetch_manager::class);


/* Fundamental Query */

/* $basic_sql_query_builder = $fetch_manager->getFundamentalBasicSqlBuilder($select_handle);
vare($basic_sql_query_builder->getFullQueryString()); */


/* Get Single by Unique Container */

/* $result = $fetch_manager->getSingleByUniqueContainer($select_handle, 'id', '1');
$model = $result->getModel();
var_dump($model->date_created); */


/* Get Single by Primary Container */

/* $result = $fetch_manager->getSingleByPrimaryContainer($select_handle, '1');
$model = $result->getModel();
var_dump($model->date_created); */


/* By Condition */

/* $condition = new Condition('title', 'Foobar');
$result = $fetch_manager->getByCondition($select_handle, $condition);

foreach( $result as $model ) {
    echo $model->title, PHP_EOL;
} */


/* By Condition Group */

/* $condition_group = ConditionGroup::fromArray([
    'date_created' => '2023-05-15 16:21:44',
    'title' => 'Foobar',
]);

$result = $fetch_manager->getByConditionGroup($select_handle, $condition_group, use_rcte: false);

foreach( $result as $model ) {
    echo $model->title, PHP_EOL;
} */


/* Filter By Values */

/* $result = $fetch_manager->filterByValues($select_handle, 'name', ['lorem', 'ipsum']);

foreach( $result as $model ) {
    echo $model->title, PHP_EOL;
} */



/* Get All */

/* $result = $fetch_manager->getAll($select_handle);

foreach( $result as $model ) {
    echo $model->title, PHP_EOL;
} */
