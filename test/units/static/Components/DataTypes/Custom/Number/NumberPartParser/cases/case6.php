<?php

declare(strict_types=1);

include __DIR__ . '/../../../../../../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');

use LWP\Components\DataTypes\Custom\Number\NumberPartParser;
use LWP\Components\DataTypes\Custom\Number\Exceptions\UniversalNumberParserException;

/* Indian Numbering System */
try {
    // One hundred thousand
    $parser = new NumberPartParser("1,00,000", [
        'group_size' => 2,
        'allow_extended_trailing_group' => true,
    ]);
    $result = true;
} catch (UniversalNumberParserException $exception) {
    $result = false;
}

Demo\assert_true($result, "Unexpected result");
