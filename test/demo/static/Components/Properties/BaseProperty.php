<?php

declare(strict_types=1);

include __DIR__ . '/../../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');

use LWP\Components\Properties\BaseProperty;
use LWP\Components\Properties\Exceptions\PropertyValueContainsErrorsException;
use LWP\Components\Properties\Exceptions\PropertyValueNotAvailableException;
use LWP\Components\Rules\StringTrimFormattingRule;
use LWP\Components\Rules\DateTimeFormattingRule;
use LWP\Components\Rules\Exceptions\UnsupportedFormattingRuleException;

$property = new BaseProperty('title', 'string');

/* Set Formatting Rules */

$string_trim_formatting_rule = new StringTrimFormattingRule();

try {
    // Supported formatting rule.
    $property->setFormattingRule($string_trim_formatting_rule);
    // Unsupported formatting rule.
    $property->setFormattingRule(new DateTimeFormattingRule(['format' => 'Y-m-d']));
} catch (UnsupportedFormattingRuleException $exception) {
    prl("Expected error: " . $dpi_output_text_formatter->format($exception->getMessage()));
}

/* Set Value */

try {
    $value = " Hello World! ";
    print "Setting value... ";
    var_dump($value);
    $accepted_value = $property->setValue($value);
    print "Accepted value: ";
    var_dump($accepted_value);
} catch (PropertyValueContainsErrorsException $exception) {
    prl("Value contains errors: ". $exception->getMessage());
} catch (\Throwable $exception) {
    prl("Could not set value: " . $exception->getMessage());
}

/* Get Value */

try {
    $value = $property->getValue();
    print "Get value: ";
    var_dump($value);
} catch (PropertyValueNotAvailableException $exception) {
    prl("Could not get value: " . $exception->getMessage());
}

/* Create From Definition Array */

$definition_array = [
    'type' => 'string',
    'default' => "Hey World!",
    'description' => "Main Title",
];

$property = BaseProperty::fromDefinitionArray('title', $definition_array);
print "Built from definition array: ";
var_dump($property->getValue());
