<?php

declare(strict_types=1);

include __DIR__ . '/../../../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');

use LWP\Components\Rules\XmlFormattingRule;
use LWP\Components\Rules\XmlFormatter;

$options = [];
$xml_formatting_rule = new XmlFormattingRule($options);
$formatter = $xml_formatting_rule->getFormatter();

Demo\assert_true(
    $formatter instanceof XmlFormatter,
    "Unexpected result"
);
