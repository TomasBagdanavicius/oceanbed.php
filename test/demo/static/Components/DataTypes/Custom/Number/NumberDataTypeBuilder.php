<?php

declare(strict_types=1);

include __DIR__ . '/../../../../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');

use LWP\Components\DataTypes\Custom\Number\NumberDataTypeBuilder;
use LWP\Components\Rules\NumberFormattingRule;

/* Formatting Rule */

$options = [
    'fractional_part_length' => 2,
    'fractional_part_separator' => '.',
    'integer_part_group_separator' => ',',
    'integer_part_group_length' => 3,
    'integer_part_trailing_group_extended' => false,
    'zerofill' => null,
];

$number_formatting_rule = new NumberFormattingRule($options);

/* Builder */
/* Public property style builder. */

$number_builder = new NumberDataTypeBuilder($number_formatting_rule, -12345, '01');
var_dump($number_builder->build());

$number_builder->integer_part = -1234;
var_dump($number_builder->build());

$number_builder->integer_part = 123456;
$number_builder->fractional_part = 789;
var_dump($number_builder->build());

$number_builder->exponent = 2;
var_dump($number_builder->build());

$number_builder->integer_part = 1000;
$number_builder->fractional_part = null;
$number_builder->exponent = null;
var_dump($number_builder->build());
