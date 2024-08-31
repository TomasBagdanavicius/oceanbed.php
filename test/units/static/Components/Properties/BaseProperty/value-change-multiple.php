<?php

declare(strict_types=1);

include __DIR__ . '/../../../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');

use LWP\Components\Properties\BaseProperty;
use LWP\Components\DataTypes\Natural\String\StringDataTypeValueContainer;
use LWP\Components\Rules\StringTrimFormattingRule;

$property = new BaseProperty('title', 'string');
// Make sure there is a formatting rule
$property->setFormattingRule(new StringTrimFormattingRule());
$string_value_container = new StringDataTypeValueContainer(" Hello World! ");
$property->setValue($string_value_container);
// Set a second value, which is the main test
$property->setValue(" Lorem ipsum dolor! ");

Demo\assert_true(
    $property->getValue() === "Lorem ipsum dolor!",
    "Property resolved with an incorrect value"
);
