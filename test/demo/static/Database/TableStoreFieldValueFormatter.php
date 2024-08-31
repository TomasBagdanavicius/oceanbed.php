<?php

declare(strict_types=1);

include __DIR__ . '/../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');
require_once(Demo\TEST_PATH . '/database-link-test.php');

$table = $database->getTable('data_types');
$formatter = $table->database->getStoreFieldValueFormatter();

echo "Date-time: ";
var_dump($formatter->formatByDataType('31st December 2021 4.30 p.m.', 'datetime'));

echo "Decimal number: ";
var_dump($formatter->formatByDataType('1 234 567,89', 'number'));
