<?php

declare(strict_types=1);

include __DIR__ . '/../../../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');
include(Demo\TEST_PATH . '/units/shared/definition-array-birthday-and-age.php');
include(Demo\TEST_PATH . '/units/shared/utilities.php');

use LWP\Components\Definitions\DefinitionCollectionSet;
use LWP\Components\Model\RelationalPropertyModel;

$date_of_birth = '1985-04-25';
$definition_collection_set = DefinitionCollectionSet::fromArray($definition_array);
$model = RelationalPropertyModel::fromDefinitionCollectionSet($definition_collection_set);
$model->birthday = $date_of_birth;

// Debug
/* var_dump($model->birthyear);
var_dump($model->date_of_birth);
var_dump($model->age); */

Demo\assert_true(
    $model->birthyear === 1985 && $model->date_of_birth === "04-25" && $model->age === (string)calculateAge($date_of_birth),
    "Unexpected result"
);
