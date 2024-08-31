<?php

declare(strict_types=1);

include __DIR__ . '/../../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');

use LWP\Components\Validators\IpAddressValidator;
use LWP\Network\Exceptions\InvalidIpAddressException;

$validator = new IpAddressValidator('1.2.3.4');
$validator->validate();

/* Error Simulation */

try {

    $validator->value = '01.2.3.4'; // Mind leading zero.
    $validator->validate();

} catch (InvalidIpAddressException $exception) {

    prl($exception->getMessage());
}
