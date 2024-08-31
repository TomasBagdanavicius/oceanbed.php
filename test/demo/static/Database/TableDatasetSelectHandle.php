<?php

declare(strict_types=1);

include __DIR__ . '/../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');
require_once(Demo\TEST_PATH . '/database-link.php');

use LWP\Database\Table;

$modifier = function (array $definitions): array {

    if (isset($definitions['type']) && $definitions['type'] === 'datetime') {
        $definitions['format'] = 'D, d M Y H:i:s';
    }

    return $definitions;
};

$dataset = $database->getTable('countries');
$select_handle = $dataset->getSelectHandle(modifiers: [
    [
        'priority' => 1,
        'modifier' => $modifier,
    ]
]);

$fetch_manager = $dataset->getFetchManager();
$action_params = $fetch_manager::getModelForActionType('read');
$data_server_context = $fetch_manager->list($select_handle, $action_params);

foreach ($data_server_context as $model) {
    echo '#', $model->id, ' ', $model->date_created, ' ', $model->title . PHP_EOL;
}
