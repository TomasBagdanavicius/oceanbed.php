<?php

declare(strict_types=1);

include __DIR__ . '/../../../../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');

use LWP\Components\Definitions\DefinitionCollectionSet;
use LWP\Components\Model\RelationalPropertyModel;
use LWP\Components\Model\EnhancedPropertyModel;
use LWP\Components\Properties\Exceptions\PropertyValueContainsErrorsException;

/* Definition Collection Set */

$definition_array = [
    'password' => [
        'type' => 'string',
        'description' => "Password.",
    ],
    'password_repeat' => [
        'type' => 'string',
        'match' => 'password', // <- Match
        'description' => "Password copy.",
    ],
];

$definition_collection_set = DefinitionCollectionSet::fromArray($definition_array);

/* Model */

$model = RelationalPropertyModel::fromDefinitionCollectionSet($definition_collection_set);
$model->setErrorHandlingMode(EnhancedPropertyModel::THROW_ERROR_IMMEDIATELY);

/* Properties */

try {

    $model->password = "hello123";
    $model->password_repeat = "hey123";

} catch (PropertyValueContainsErrorsException $exception) {

    prl("Expected error: " . $exception->getMessage() . " " . $exception->getPrevious()->getMessage());
}

echo "Does \"password_repeat\" contain errors? ";
var_dump($model->getPropertyByName('password_repeat')->hasErrors());

var_dump($model->getValuesWithMessages());

try {

    $model->password_repeat = "hello123";

} catch (PropertyValueContainsErrorsException $exception) {

    // Expecting no exception.
}

var_dump($model->getValuesWithMessages());
