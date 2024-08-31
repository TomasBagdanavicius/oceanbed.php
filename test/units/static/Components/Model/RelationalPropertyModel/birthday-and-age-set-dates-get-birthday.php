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
$model->date_of_birth = '04-25';
$birthday = $model->birthday;

Demo\assert_true($birthday === "1985-04-25", "Expected 1985-04-25, got $birthday");
