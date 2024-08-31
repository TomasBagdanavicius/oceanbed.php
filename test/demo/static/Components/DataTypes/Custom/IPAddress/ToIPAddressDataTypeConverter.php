<?php

declare(strict_types=1);

include __DIR__ . '/../../../../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');

use LWP\Components\DataTypes\Custom\IpAddress\ToIpAddressDataTypeConverter;

$converter = new ToIpAddressDataTypeConverter();

/* Create From */

// From long.
$ip_address_value = $converter::convert(16909060); // 1.2.3.4
print "From a timestamp: ";
var_dump($ip_address_value->getValue());
