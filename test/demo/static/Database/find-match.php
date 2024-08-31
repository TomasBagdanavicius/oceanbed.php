<?php

declare(strict_types=1);

include __DIR__ . '/../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');
require_once(Demo\TEST_PATH . '/database-link.php');

use LWP\Database\Table;

$dataset = $database->getTable('countries');

$select_handle = $dataset->getSelectHandle();
$fetch_manager = $dataset->getFetchManager();

$model = $dataset->getModel();
$dataset->setupModelPopulateCallbacks($model);

$model->title = "Antarctica";
$model->short_title = "New Land";
$model->name = 'new-land';
$model->iso_3166_1_alpha_2_code = 'NN';
$model->iso_3166_1_alpha_3_code = 'RNL';
$model->iso_3166_1_numeric_code = '123';

$match = $fetch_manager->findMatch($select_handle, $model);

if ($match) {
    echo "Match found: ";
    pr($match->getOne());
} else {
    prl("No match was found");
}
