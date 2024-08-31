<?php

declare(strict_types=1);

include __DIR__ . '/../../../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');

use LWP\Components\Rules\NumberFormatter;
use LWP\Components\Rules\NumberFormattingRule;

$options = [
    'fractional_part_length' => 2,
    'fractional_part_separator' => ',',
    'integer_part_group_length' => 3,
    'integer_part_group_separator' => ' ',
    'zerofill' => null,
    'integer_part_trailing_group_extended' => false,
];
$number_formatting_rule = new NumberFormattingRule($options);
$formatter = new NumberFormatter($number_formatting_rule);

Demo\assert_true(
    $formatter->format(1000.1) === "1 000,10",
    "Unexpected result"
);
