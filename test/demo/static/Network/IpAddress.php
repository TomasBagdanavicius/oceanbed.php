<?php

declare(strict_types=1);

include __DIR__ . '/../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');

use LWP\Network\IpAddress;
use LWP\Network\Exceptions\InvalidIpAddressException;

$ip_address = new IpAddress('1.2.3.4');

echo "Version number: ";
var_dump($ip_address->getVersion());
echo "Long representation: ";
var_dump($ip_address->getLong());
echo "Hexadecimal representation: ";
var_dump($ip_address->getHexadecimal());

/* Create From */

echo "From long: ";
var_dump(IpAddress::fromLong(16909060)?->__toString());
echo "From hexadecimal: ";
var_dump(IpAddress::fromHexadecimal('01020304')?->__toString());

/* Invalid Value */

try {
    $ip_address = new IpAddress('01.2.3.4'); // Mind leading zero.
} catch (InvalidIpAddressException $exception) {
    prl("Expected error: " . $exception->getMessage());
}
