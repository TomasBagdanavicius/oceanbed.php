<?php

declare(strict_types=1);

include __DIR__ . '/../../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');

use LWP\Components\DataTypes\Custom\DateTime\DateTimeDataTypeValueContainer;
use LWP\Components\Properties\BaseProperty;
use LWP\Components\Rules\DateTimeFormattingRule;
use LWP\Components\DataTypes\Custom\DateTime\DateTimeDataTypeValueDescriptor;
use LWP\Common\Enums\ValidityEnum;

$date_time_formatting_rule = new DateTimeFormattingRule([
    'format' => 'Y-m-d H:i:s',
]);

$value_string = '2022-01-01 08:00:00';
$value_string_alt = 'Wed, 19 Oct 2022 08:40:48 +0000';

$value_descriptor = new DateTimeDataTypeValueDescriptor(
    ValidityEnum::VALID,
    $date_time_formatting_rule
);

$value_descriptor_alt = new DateTimeDataTypeValueDescriptor(
    ValidityEnum::VALID,
    new DateTimeFormattingRule([
        'format' => 'D, j M Y H:i:s O',
    ])
);

// Matching format - value shouldn't be formatted again in property.
$date_time_data_type_value_container = new DateTimeDataTypeValueContainer($value_string, $value_descriptor);
// Different format.
#$date_time_data_type_value_container = new DateTimeDataTypeValueContainer($value_string_alt, $value_descriptor_alt);

$base_property = new BaseProperty('prop_1', 'datetime');
$base_property->setFormattingRule($date_time_formatting_rule);
$base_property->setValue($date_time_data_type_value_container);

try {
    $property_value = $base_property->getValue();
} catch (\Throwable $exception) {
    prl("Error: " . $exception->getMessage());
}

var_dump($property_value);
