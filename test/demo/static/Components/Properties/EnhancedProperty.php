<?php

declare(strict_types=1);

include __DIR__ . '/../../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');

use LWP\Components\Properties\EnhancedProperty;
use LWP\Components\Attributes\NoDefaultValueAttribute;
use LWP\Common\Enums\AccessLevelsEnum;
use LWP\Components\Properties\Exceptions\PropertySetAccessRestrictedException;

$enhanced_property = new EnhancedProperty('title', 'string');

/* Set Value */

try {
    $value = "Hello World!";
    print "Setting value... ";
    var_dump($value);
    $accepted_value = $enhanced_property->setValue($value);
    print "Accepted value: ";
    var_dump($accepted_value);
} catch (PropertyValueContainsErrorsException $exception) {
    prl("Value contains errors: ". $exception->getMessage());
} catch (Throwable $exception) {
    prl("Could not set value: " . $exception->getMessage());
}

/* Nullable */

$enhanced_property = new EnhancedProperty('title', 'string', nullable: true);

try {
    print "Getting nullable property's value: ";
    var_dump($enhanced_property->getValue());
    $enhanced_property->setValue(null);
    print "After setting to NULL: ";
    var_dump($enhanced_property->getValue());
} catch (Throwable $exception) {
    prl("Nullable test result: " . $enhanced_property->getValue());
}

/* Set Access Level Restrictions */

$enhanced_property = new EnhancedProperty('title', 'string', set_access: AccessLevelsEnum::PRIVATE);

try {
    $enhanced_property->setValue("Hello World!");
    print "Getting value: ";
    var_dump($enhanced_property->getValue());
} catch (PropertySetAccessRestrictedException $exception) {
    prl("Expected error: " . $exception->getMessage());
}

/* Create From Definition Array */

$definition_array = [
    'type' => 'string',
    'default' => "Hey World!",
    'description' => "Main Title",
];

$enhanced_property = EnhancedProperty::fromDefinitionArray('title', $definition_array);
print "Built from definition array: ";
var_dump($enhanced_property->getValue());
