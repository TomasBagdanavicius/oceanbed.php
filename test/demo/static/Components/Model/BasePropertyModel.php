<?php

declare(strict_types=1);

include __DIR__ . '/../../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');

use LWP\Common\Exceptions\AbortException;
use LWP\Components\DataTypes\Natural\String\StringDataTypeValueContainer;
use LWP\Components\Model\BasePropertyModel;
use LWP\Components\Properties\BaseProperty;
use LWP\Components\Definitions\DefinitionCollectionSet;
use LWP\Components\Properties\EnhancedProperty;
use LWP\Components\Model\Exceptions\PropertyAddException;

$property1 = new BaseProperty('title', 'string');
#$property1 = new EnhancedProperty('title', 'string'); // Incorrect property class (EnhancedProperty).
$property2 = new BaseProperty('name', 'string', new StringDataTypeValueContainer('default'));

/* Model */

$base_property_model = new BasePropertyModel();

$base_property_model->addProperty($property1);
$base_property_model->addProperty($property2);

$base_property_model->title = "Title";
$base_property_model['name'] = "title";

var_dump($base_property_model->getDefaultValues());
var_dump($base_property_model->getValues());

/* Create From Definition Collection Set */

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

$base_property_model = BasePropertyModel::fromDefinitionCollectionSet($definition_collection_set);

var_dump($base_property_model->title);

/* Add Property Callback */

$base_property_model = new BasePropertyModel();

$base_property_model->onBeforePropertyAdd(function (BaseProperty $property, BasePropertyModel $model): BaseProperty {

    throw new AbortException("Abort");

    return $property;

});

try {
    $base_property_model->addProperty($property1);
} catch (PropertyAddException $exception) {
    prl("Expected error: " . $exception->getMessage());
}
