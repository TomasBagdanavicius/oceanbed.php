<?php

declare(strict_types=1);

include __DIR__ . '/../../../../../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');

use LWP\Components\DataTypes\Custom\Number\NumberPartParser;
use LWP\Components\DataTypes\Custom\Number\Exceptions\UniversalNumberParserException;

try {
    $number_part_parser = new NumberPartParser("123 456,789");
    $result = false;
} catch (UniversalNumberParserException $exception) {
    $result = true;
}

Demo\assert_true($result, "Unexpected result");
