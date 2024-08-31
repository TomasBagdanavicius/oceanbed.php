<?php

declare(strict_types=1);

include __DIR__ . '/../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');

use LWP\Common\OptionManager;
use LWP\Common\Exceptions\ElementNotFoundException;

$default = [
    'one' => 'Earth',
    'two' => 'Apples',
    'three' => null,
];

$custom = [
    'two' => 'Eyes',
];

$allowed_options = [
    'one',
    'two',
    'three',
    'four',
    'five',
];

$options_manager = new OptionManager($custom, $default, $allowed_options);

$options_manager->four = "Corners";
$options_manager->set('five', null);

if ($options_manager->containsKey('three')) {
    var_dump($options_manager->get('three'));
}

try {
    $options_manager->get('six');
} catch (ElementNotFoundException $exception) {
    echo $exception->getMessage(), PHP_EOL;
}

var_dump($options_manager->toArray());
