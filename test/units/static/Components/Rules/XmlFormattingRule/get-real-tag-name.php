<?php

declare(strict_types=1);

include __DIR__ . '/../../../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');

use LWP\Components\Rules\XmlFormattingRule;

$attribute_name = 'my-attr';
$options = [
    'real_tagname_attr_name' => $attribute_name,
];
$xml_formatting_rule = new XmlFormattingRule($options);

Demo\assert_true(
    $xml_formatting_rule->getRealTagnameAttrName() === $attribute_name,
    "Unexpected result"
);
