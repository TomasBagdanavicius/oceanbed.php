<?php

declare(strict_types=1);

include __DIR__ . '/../../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');

use LWP\Components\Definitions\DefinitionCollection;
use LWP\Components\Definitions\TypeDefinition;
use LWP\Components\Definitions\DescriptionDefinition;

$definition_collection = new DefinitionCollection();

$definition_collection->add(new TypeDefinition('string'));
$definition_collection->add(new DescriptionDefinition("This is my comment."));

echo "Has primary definition: ";
var_dump($definition_collection->hasPrimaryDefinition());
echo "Primary name: ";
var_dump($definition_collection->getPrimaryDefinition()?->getName());
echo "Type value: ";
var_dump($definition_collection->getTypeValue());

/* From Array */

$array = [
    'type' => 'string',
    'max' => 255,
    'description' => "Main title.",
];

$definition_collection = DefinitionCollection::fromArray($array);
echo "Created from array count: ";
var_dump($definition_collection->count());
