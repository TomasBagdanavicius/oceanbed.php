<?php

declare(strict_types=1);

include __DIR__ . '/../../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');

use LWP\Components\Model\RelationalPropertyModel;
use LWP\Components\Properties\RelationalProperty;
use LWP\Components\Properties\Exceptions\PropertyValueNotAvailableException;

$relational_model = new RelationalPropertyModel();

$relational_property_1 = new RelationalProperty($relational_model, 'prop_1', 'string');

$relational_model_2 = clone $relational_model;

$relational_model_2->prop_1 = "Hello World!";

try {

    // Since property value was assigned through "relational_model_2", this should be empty, because cloned model must not affect the original model.
    var_dump($relational_model->prop_1);

} catch (PropertyValueNotAvailableException $exception) {

    prl("Expected error: " . $exception->getMessage());
}
