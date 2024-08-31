<?php

declare(strict_types=1);

include __DIR__ . '/../../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');

use LWP\Common\Enums\AccessLevelsEnum;
use LWP\Components\Properties\EnhancedProperty;
use LWP\Components\Model\EnhancedPropertyModel;
use LWP\Components\Definitions\DefinitionCollectionSet;
use LWP\Components\DataTypes\Natural\String\StringDataTypeValueContainer;
use LWP\Components\Properties\Exceptions\PropertySetAccessRestrictedException;

/* Properties */

$property1 = new EnhancedProperty('title', 'string');
$property2 = new EnhancedProperty('name', 'string', new StringDataTypeValueContainer('default-value'));

/* Model */

$enhanced_property_model = new EnhancedPropertyModel();

$enhanced_property_model->addProperty($property1);
$enhanced_property_model->addProperty($property2);

$enhanced_property_model->title = "Title";
$enhanced_property_model->name = "title";

var_dump($enhanced_property_model->getDefaultValues());
var_dump($enhanced_property_model->getValues());

/* Create From Definition Collection Set */

$definition_collection_set = DefinitionCollectionSet::fromArray([
    'title' => [
        'type' => 'string',
        'default' => "Title",
    ],
    'name' => [
        'type' => 'string',
    ],
]);

$enhanced_property_model = EnhancedPropertyModel::fromDefinitionCollectionSet($definition_collection_set);

var_dump($enhanced_property_model->title);
