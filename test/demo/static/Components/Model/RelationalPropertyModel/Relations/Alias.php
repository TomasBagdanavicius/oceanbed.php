<?php

declare(strict_types=1);

include __DIR__ . '/../../../../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');

use LWP\Components\Definitions\DefinitionCollectionSet;
use LWP\Components\Model\RelationalPropertyModel;

/* Definition Collection Set */

$definition_array = [
    'title' => [
        'type' => 'string',
        'default' => 'Default Title',
        'description' => "Main title.",
    ],
    'name' => [
        'type' => 'string',
        'alias' => 'title', // <- Alias
        'description' => "Tag name.",
    ],
];

$definition_collection_set = DefinitionCollectionSet::fromArray($definition_array);

/* Model */

$model = RelationalPropertyModel::fromDefinitionCollectionSet($definition_collection_set);

/* Properties */

$model->title = "My Title";

var_dump($model->getValuesWithMessages());
