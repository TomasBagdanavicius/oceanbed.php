<?php

declare(strict_types=1);

include __DIR__ . '/../../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');

use LWP\Network\Domain\DomainDbDataReader;

require_once(Demo\TEST_PATH . '/database-link.php');

$data_reader = new DomainDbDataReader($database->getTable('domains'));

var_dump($data_reader->containsEntry('com'));
var_dump($data_reader->getPublicSuffix('www.domain.co.uk'));
