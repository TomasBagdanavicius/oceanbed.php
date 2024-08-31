<?php

declare(strict_types=1);

include __DIR__ . '/../../../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');

use LWP\Components\Rules\JsonFormattingRule;
use LWP\Components\Rules\JsonFormatter;

/* Formatting Rule */

$json_formatting_rule = new JsonFormattingRule();
$json_formatter = new JsonFormatter($json_formatting_rule);
$format = $json_formatter->format(["foo", "bar", "baz"]);

Demo\assert_true(
    $format === '["foo","bar","baz"]',
    "Unexpected result"
);
