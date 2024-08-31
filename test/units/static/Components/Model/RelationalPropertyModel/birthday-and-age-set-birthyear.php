<?php

declare(strict_types=1);

include __DIR__ . '/../../../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');
include(Demo\TEST_PATH . '/units/shared/definition-array-birthday-and-age.php');

use LWP\Components\Definitions\DefinitionCollectionSet;
use LWP\Components\Model\RelationalPropertyModel;

$definition_collection_set = DefinitionCollectionSet::fromArray($definition_array);
$model = RelationalPropertyModel::fromDefinitionCollectionSet($definition_collection_set);
$model->birthyear = 1985;

// Debug
/* var_dump($model->date_of_birth);
var_dump($model->birthday);
var_dump($model->age); */

Demo\assert_true(
    $model->date_of_birth === null && $model->birthday === null && $model->age === null,
    "Unexpected result"
);
