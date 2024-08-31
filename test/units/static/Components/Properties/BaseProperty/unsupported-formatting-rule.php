<?php

declare(strict_types=1);

include __DIR__ . '/../../../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');

use LWP\Components\Properties\BaseProperty;
use LWP\Components\Rules\{
    DateTimeFormattingRule,
    Exceptions\UnsupportedFormattingRuleException
};

// Unsupported formatting rule.

$base_property = new BaseProperty('prop1', 'string');

$expected_thrown = false;

try {

    // Unsupported date time formatting rule.
    $base_property->setFormattingRule(
        new DateTimeFormattingRule(['format' => 'Y-m-d'])
    );

} catch (UnsupportedFormattingRuleException $exception) {

    $expected_thrown = true;
}

Demo\assert_true(
    $expected_thrown,
    "Expected UnsupportedFormattingRuleException not thrown"
);
