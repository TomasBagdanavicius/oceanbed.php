<?php

declare(strict_types=1);

include __DIR__ . '/../../../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');

use LWP\Components\Rules\StringTrimFormattingRule;

$options = [
    'side' => StringTrimFormattingRule::SIDE_BOTH,
    'mask' => 'AB',
    'mask_as' => StringTrimFormattingRule::MASK_AS_SUBSTRING,
    'repeatable' => true,
];
$string_trim_formatting_rule = new StringTrimFormattingRule($options);

Demo\assert_true(
    $string_trim_formatting_rule->getMaskAs() === StringTrimFormattingRule::MASK_AS_SUBSTRING,
    "Unexpected result"
);
