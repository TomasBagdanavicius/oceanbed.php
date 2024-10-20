<?php

declare(strict_types=1);

include __DIR__ . '/../../../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');

use LWP\Components\Rules\ConcatFormattingRule;
use LWP\Components\Rules\ConcatFormatter;

$options = [
    'separator' => ',',
];
$concat_formatting_rule = new ConcatFormattingRule($options);
$formatter = $concat_formatting_rule->getFormatter();

Demo\assert_true(
    $formatter instanceof ConcatFormatter,
    "Unexpected result"
);
