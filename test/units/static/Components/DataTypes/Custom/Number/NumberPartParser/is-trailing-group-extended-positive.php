<?php

declare(strict_types=1);

include __DIR__ . '/../../../../../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');

use LWP\Components\DataTypes\Custom\Number\NumberPartParser;
use LWP\Components\DataTypes\Custom\Number\Exceptions\UniversalNumberParserException;

$number_part_parser = new NumberPartParser("12 345 6789", [
    'allow_extended_trailing_group' => true,
]);

Demo\assert_true(
    $number_part_parser->isTrailingGroupExtended() === true,
    "Unexpected result"
);
