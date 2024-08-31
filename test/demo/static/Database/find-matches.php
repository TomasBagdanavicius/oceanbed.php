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
use LWP\Components\Model\ModelCollection;

$dataset = $database->getTable('countries');

$select_handle = $dataset->getSelectHandle();
$fetch_manager = $dataset->getFetchManager();

$model1 = $dataset->getModel();
$dataset->setupModelPopulateCallbacks($model1);

$model2 = clone $model1;

$model1->title = "Antarctica";
$model1->short_title = "New Land";
$model1->name = 'new-land';
$model1->iso_3166_1_alpha_2_code = 'NN';
$model1->iso_3166_1_alpha_3_code = 'RNL';
$model1->iso_3166_1_numeric_code = '123';

$model2->title = "Distant Island";
$model2->short_title = "Island";
$model2->name = 'distant-island';
$model2->iso_3166_1_alpha_2_code = 'DI';
$model2->iso_3166_1_alpha_3_code = 'RDI';
$model2->iso_3166_1_numeric_code = '321';

$model_collection = new ModelCollection();
$model_collection->add($model1);
$model_collection->add($model2);

$result = $fetch_manager->findMatches($select_handle, $model_collection);
echo "Found count: ";
if ($result) {
    var_dump($result->count());
} else {
    echo '0';
}
