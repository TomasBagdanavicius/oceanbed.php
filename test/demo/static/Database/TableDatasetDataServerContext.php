<?php

declare(strict_types=1);

include __DIR__ . '/../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');
require_once(Demo\TEST_PATH . '/database-link.php');

use LWP\Database\Table;
use LWP\Components\Datasets\Attributes\SelectAllAttribute;

$table = $database->getTable('countries');

$select_handle = $table->getSelectHandle();
$fetch_manager = $table->getFetchManager();

/* List */

$action_params = $fetch_manager::getModelForActionType('read');
#$action_params->page_number = 2;
#$action_params->limit = 2;
$action_params->search_query = 'sao';
#$action_params->sort = 'id';
#$action_params->order = 'desc';

pr($action_params->getValues());

$data_server_context = $fetch_manager->list($select_handle, $action_params);

$i = 1;
echo PHP_EOL;
foreach ($data_server_context as $model) {
    echo '#', $model->id, ' ', $model->date_created, ' ', $model->title . PHP_EOL;
    $i++;
}

include(Demo\TEST_PATH . '/demo/shared/generic-pager.php');
