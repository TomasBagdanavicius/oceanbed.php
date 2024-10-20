<?php

declare(strict_types=1);

include __DIR__ . '/../../../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');

use LWP\Components\Rules\XmlFormattingRule;
use LWP\Components\Rules\XmlFormatter;

$options = [
    'real_tagname_attr_name' => 'my-attr',
];
$xml_formatting_rule = new XmlFormattingRule($options);
$formatter = $xml_formatting_rule->getFormatter();
$formatted_xml_string = $formatter->format([
    'foo' => 'bar',
    'bar' => 'baz',
    'i/o' => 'lorem',
]);
$expected_string = <<<EOT
<?xml version="1.0"?>
<data><foo>bar</foo><bar>baz</bar><item my-attr="i/o">lorem</item></data>

EOT;

Demo\assert_true(
    $formatted_xml_string === $expected_string,
    "Unexpected result"
);
