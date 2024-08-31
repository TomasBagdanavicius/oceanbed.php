<?php

declare(strict_types=1);

include __DIR__ . '/../../../../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');

use LWP\Components\Rules\TagnameFormattingRule;

$options = [
    'max_length' => 7,
];
$formatting_rule = new TagnameFormattingRule($options);
$formatter = $formatting_rule->getFormatter();

Demo\assert_true(
    // Greedy and goes above 7 chars to reach end of part
    $formatter->format("Hello World!") === 'hello-world',
    "Unexpected result"
);
