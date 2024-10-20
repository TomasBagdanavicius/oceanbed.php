<?php

declare(strict_types=1);

include __DIR__ . '/../../../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');

use LWP\Components\Rules\CalcFormattingRule;

$options = [
    'subject' => 'age',
];
$calc_formatting_rule = new CalcFormattingRule($options);

Demo\assert_true(
    $calc_formatting_rule instanceof CalcFormattingRule,
    "Unexpected result"
);
