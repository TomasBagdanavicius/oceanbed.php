<?php

declare(strict_types=1);

include __DIR__ . '/../../../../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');

use LWP\Components\Properties\EnhancedProperty;

// Accept null value from closure.

$enhanced_property = new EnhancedProperty(
    'prop1',
    'string',
    function (): null {
        return null;
    },
    nullable: true
);

Demo\assert_true(
    true,
    "Null value was not accepted"
);
