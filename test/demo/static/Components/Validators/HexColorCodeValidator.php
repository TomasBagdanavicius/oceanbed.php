<?php

declare(strict_types=1);

include __DIR__ . '/../../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');

use LWP\Components\Validators\HexColorCodeValidator;
use LWP\Common\Exceptions\InvalidValueException;

$validator = new HexColorCodeValidator('#ff0000');
$validator->validate();

/* Error Simulation */

try {

    $validator->value = '#z11111';
    $validator->validate();

} catch (InvalidValueException $exception) {

    prl("Expected error: " . $exception->getMessage());
}
