<?php

declare(strict_types=1);

include __DIR__ . '/../../../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');

use LWP\Components\Rules\TagnameFormattingRule;
use LWP\Components\Rules\TagnameFormatter;

$options = [
    'separator' => '_',
    'max_length' => 255,
];
$formatting_rule = new TagnameFormattingRule($options);
$formatter = $formatting_rule->getFormatter();

Demo\assert_true($formatter instanceof TagnameFormatter, "Unexpected result");
