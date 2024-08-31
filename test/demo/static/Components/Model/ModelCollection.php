<?php

declare(strict_types=1);

include __DIR__ . '/../../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');

use LWP\Components\Definitions\DefinitionCollectionSet;
use LWP\Components\Model\ModelCollection;
use LWP\Components\Model\BasePropertyModel;

/* Create Models */

$definition_collection_set = DefinitionCollectionSet::fromArray([
    'title' => [
        'type' => 'string',
        'default' => "Title",
        'description' => "Main title.",
    ],
    'name' => [
        'type' => 'string',
        'description' => "Canonical name.",
    ],
]);

$base_property_model_1 = BasePropertyModel::fromDefinitionCollectionSet($definition_collection_set);
$base_property_model_1->title = "Title 1";
$base_property_model_1->name = 'title-1';

$base_property_model_2 = clone $base_property_model_1;
$base_property_model_2->title = "Title 2";
$base_property_model_2->name = 'title-2';

/* Model Collection */

$model_collection = new ModelCollection();
$model_collection->add($base_property_model_1);
$model_collection->add($base_property_model_2);

echo "Count models: ";
var_dump($model_collection->count());

foreach ($model_collection as $name => $model) {
    echo $model->title, ' ', $model->name, PHP_EOL;
}
