<?php

declare(strict_types=1);

include __DIR__ . '/../../../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');

use LWP\Components\Rules\TagnameFormattingRule;

$options = [
    'separator' => '_',
    'max_length' => 255,
];
$formatting_rule = new TagnameFormattingRule($options);

Demo\assert_true($formatting_rule->getSeparator() === '_', "Unexpected result");
