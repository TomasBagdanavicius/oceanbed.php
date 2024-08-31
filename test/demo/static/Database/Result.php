<?php

declare(strict_types=1);

include __DIR__ . '/../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');
require_once(Demo\TEST_PATH . '/database-link-test.php');

use LWP\Filesystem\Path\PosixPath;

$table = $database->getTable('test');

$result = $table->getAllResult();
#$result = $table->getSelectedAllResult(['title']);

#$result->getOne();
#print_r( $result->toArray() );
#$result->getCollection();

/*$result->each(function( \stdClass $row ) {
    pr($row);
});*/

/* Iterator */
$iterator = $result->getIterator();

foreach ($iterator as $key => $val) {

    pr($val);
}

/* To CSV
$file_path = PosixPath::getFilePathInstance('../tmp/database-result-export.csv');
$result->putDataToCsvFile($file_path); */
