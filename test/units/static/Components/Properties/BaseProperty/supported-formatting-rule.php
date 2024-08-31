<?php

declare(strict_types=1);

include __DIR__ . '/../../../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');

use LWP\Components\Properties\BaseProperty;
use LWP\Components\Rules\{
    StringTrimFormattingRule,
    Exceptions\UnsupportedFormattingRuleException
};

// Supported formatting rule.

$base_property = new BaseProperty('prop1', 'string');

$base_property->setFormattingRule(new StringTrimFormattingRule());

$accepted_value = $base_property->setValue(" Hello World! ");

$value = $base_property->getValue();

Demo\assert_true(
    $value === "Hello World!",
    "Incorrect value"
);
