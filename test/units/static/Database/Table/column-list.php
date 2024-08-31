<?php

declare(strict_types=1);

include __DIR__ . '/../../../../../var/config.php';
require_once $user_config['stonetable_path'] . '/src/web/demo-page-init.php';

Demo\start();

/*** Demo Code ***/

require_once(Demo\SRC_PATH . '/Autoload.php');
require_once(Demo\TEST_PATH . '/database-link-test.php');

use LWP\Database\Table;

\LWP\Autoload::loadFileByNamespaceName('LWP\Common\Array\Arrays', false);
use function LWP\Common\Array\Arrays\valuesMatch;

$table = $database->getTable('static');
$expected_columns = [
    'id',
    'date_created',
    'title',
    'name',
];

Demo\assert_true(
    // The order of columns coming from the database might fluctuate
    valuesMatch($expected_columns, $table->getColumnList(force: true)),
    "Unexpected list"
);
