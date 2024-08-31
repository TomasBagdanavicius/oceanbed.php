<?php

declare(strict_types=1);

include __DIR__ . '/../../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');

use LWP\Components\Validators\DateTimeValidator;
use LWP\Components\DataTypes\Custom\DateTime\Exceptions\InvalidDateTimeException;

$validator = new DateTimeValidator('2022-01-01 08:00:00');
echo "Result: ";
var_dump($validator->validate());

/* Simulate Error */

try {

    $validator->value = '2022-01-01 08:00:000'; // Trailing zero.
    var_dump($validator->validate());

} catch (InvalidDateTimeException $exception) {

    prl("Expected error: " . $exception->getMessage());
}

/* With Format */

try {

    $validator->value = '2022-01-01 08:00:00';
    #$validator->value = 'Wed, 17 Aug 2022 06:15:22 +0000'; // Correct value.
    var_dump($validator->validate('D, d M Y H:i:s O'));

} catch (InvalidDateTimeException $exception) {

    prl("Expected error: " . $exception->getMessage());
}
