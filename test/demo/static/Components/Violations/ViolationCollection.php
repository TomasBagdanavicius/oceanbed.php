<?php

declare(strict_types=1);

include __DIR__ . '/../../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');

use LWP\Components\Violations\ViolationCollection;
use LWP\Components\Violations\MaxSizeViolation;

$violation_collection = new ViolationCollection();

$max_size_violation = new MaxSizeViolation(10, 12);

$violation_collection->add($max_size_violation);

/* Convert to Message Collection */

$message_collection = $violation_collection->toErrorMessageCollection();

foreach ($message_collection as $key => $message) {

    prl($message->text);
}
