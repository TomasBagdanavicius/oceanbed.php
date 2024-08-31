<?php

declare(strict_types=1);

include __DIR__ . '/../../../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');

use LWP\Components\Rules\TagnameFormattingRule;
use LWP\Components\Properties\BaseProperty;
use LWP\Components\DataTypes\Natural\String\StringDataTypeValueContainer;
use LWP\Components\DataTypes\Natural\String\StringDataTypeValueDescriptor;
use LWP\Common\Enums\ValidityEnum;

$tagname_formatting_rule = new TagnameFormattingRule([
    'separator' => '-',
    'max_length' => 255,
]);
$value_string = "lorem ipsum dolor";

$base_property = new BaseProperty('prop_1', 'string');
$base_property->setFormattingRule($tagname_formatting_rule);
// No value descriptor
$string_data_type_value_container = new StringDataTypeValueContainer($value_string);

$base_property->setValue($string_data_type_value_container);

Demo\assert_true($base_property->getValue() === "lorem-ipsum-dolor", "Unexpected result");
