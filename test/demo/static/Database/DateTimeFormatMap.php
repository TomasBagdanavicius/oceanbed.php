<?php

declare(strict_types=1);

include __DIR__ . '/../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');

use LWP\Database\DateTimeFormatMap;

$date_time_format_map = new DateTimeFormatMap();

var_dump($date_time_format_map::ESCAPE_CHAR);
print_r($date_time_format_map->getPrimaryMap());
print_r($date_time_format_map->getSecondaryMap());
