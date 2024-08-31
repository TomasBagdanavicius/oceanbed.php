<?php

declare(strict_types=1);

include __DIR__ . '/../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');

use LWP\Database\DatabaseDescriptor;

require_once(Demo\TEST_PATH . '/database-link-test.php');

$descriptor = $database->getDescriptor();

/* Getter */

pr($descriptor->getSupportedFormattingRulesMap());
prl($descriptor->getSyntaxBuilderClassName('LWP\Components\Rules\StringTrimFormattingRule'));
pr($descriptor->getSupportedFormattingRules());
var_dump($descriptor->isSupportedFormattingRule(LWP\Components\Rules\StringTrimFormattingRule::class));

/* Setter */

pr($descriptor->getSetterFormattingRuleMap());
var_dump($descriptor->getSetterFormattingRuleByClassName(LWP\Components\Rules\DateTimeFormattingRule::class)::class);
prl($descriptor->getSetterFormattingRuleForDataType('datetime')::class);
prl($descriptor->getSetterFormattingRuleForDataType('number')::class);
