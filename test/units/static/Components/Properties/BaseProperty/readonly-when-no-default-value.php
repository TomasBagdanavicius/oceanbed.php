<?php

declare(strict_types=1);

include __DIR__ . '/../../../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');

use LWP\Components\Properties\BaseProperty;
use LWP\Common\Exceptions\ReadOnlyException;

$base_property = new BaseProperty('prop1', 'string', readonly: true);

try {
    $base_property->setValue("Hello World!");
} catch (\Throwable $exception) {

}

$expected_thrown = false;

try {
    $base_property->setValue("Lorem ipsum");
} catch (ReadOnlyException) {
    $expected_thrown = true;
}

Demo\assert_true(
    $expected_thrown,
    "Expected LogicException not thrown"
);
