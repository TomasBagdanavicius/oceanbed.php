<?php

declare(strict_types=1);

include __DIR__ . '/../../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');

use LWP\Components\Validators\VariableTypeValidator;
use LWP\Common\Exceptions\NotFoundException;

$validator = new VariableTypeValidator('string');
$validator->validate();

try {

    $validator->value = ['float', 'boolean'];
    $validator->validate();

} catch (NotFoundException $exception) {

    prl($exception->getMessage());
}
