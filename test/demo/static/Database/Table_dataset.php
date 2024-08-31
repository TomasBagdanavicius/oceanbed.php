<?php

declare(strict_types=1);

include __DIR__ . '/../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');
require_once(Demo\TEST_PATH . '/database-link-test.php');

$dataset = $database->getTable('static');

echo "Dataset name: ";
var_dump($dataset->getDatasetName());

echo "Abbreviation: ";
var_dump($dataset->getAbbreviation());

$dataset->setAbbreviation('fdd1');
echo "Set abbreviation: ";
var_dump($dataset->getAbbreviation());

echo "Container list: ";
pr($dataset->getContainerList());

/* echo "Next value for unique container: ";
var_dump($dataset->getNextUniqueContainerValue('title', 'foobar', 255)); */
