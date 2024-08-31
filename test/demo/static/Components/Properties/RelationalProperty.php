<?php

declare(strict_types=1);

include __DIR__ . '/../../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');

use LWP\Common\Enums\AccessLevelsEnum;
use LWP\Components\DataTypes\Natural\String\StringDataTypeValueContainer;
use LWP\Components\Properties\RelationalProperty;
use LWP\Components\Model\RelationalPropertyModel;
use LWP\Components\Properties\Exceptions\PropertyDependencyException;
use LWP\Components\Definitions\DefinitionCollection;
use LWP\Components\Definitions\DefinitionCollectionSet;
use LWP\Components\Model\SharedAmounts\SharedAmountCollection;
use LWP\Components\Properties\Exceptions\PropertyStateException;
use LWP\Components\Constraints\MaxSizeConstraint;

$relational_model = new RelationalPropertyModel();

$relational_property = new RelationalProperty($relational_model, 'title', 'string');

echo "Has value: ";
var_dump($relational_property->hasValue());


/* Dependencies */

$relational_model = new RelationalPropertyModel();

$relational_property_1 = new RelationalProperty($relational_model, 'prop_1', 'string');
$relational_property_2 = new RelationalProperty($relational_model, 'prop_2', 'string');
$relational_property_3 = new RelationalProperty($relational_model, 'prop_3', 'string', dependencies: [
    'prop_1',
    'prop_2',
]);

echo "Property 3 state: ";
var_dump($relational_property_3->getState());
echo "Property 3 dependency list: ";
pr($relational_property_3->getDependencyList());

$relational_property_1->setValue("Property 1 value");

$relational_property_3->onBeforeSetValue(function (mixed $property_value): mixed {

    echo "Intercepted property 3 value: ";
    var_dump($property_value);

    return $property_value;

});

try {

    $relational_property_3->setValue("Property 3 value");

} catch (PropertyDependencyException $exception) {

    prl("Expected error: " . $exception->getMessage());
}

$relational_property_2->setValue("Property 2 value");

echo "Property 3 value: ";
var_dump($relational_property_3->getValue());
